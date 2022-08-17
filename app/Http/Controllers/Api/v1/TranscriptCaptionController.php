<?php

namespace App\Http\Controllers\Api\v1;

use App\Jobs\StartTranscription;
use App\Jobs\UploadFileToCloudStorage;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\GetTranscription;
use App\Models\MediaTranscript;
use App\Libraries\FileHandler;
use App\Models\ThirdPartyApiCall;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Http\Controllers\Api\v1\Factories\TimeTextFactory;
use App\Repositories\Interfaces\CaptionRepositoryInterface;
use App\Http\Controllers\Api\v1\Factories\TranscriptionFactory;
use App\Repositories\Interfaces\TranscriptCaptionRepositoryInterface;
use App\Events\AutoTranscriptionGenerated;

use App\Repositories\Interfaces\WorkflowProcessRepositoryInterface;

class TranscriptCaptionController extends Controller
{
    private $transcriptCaptionRepository;
    private $captionRepository;
    private $mediaRepository;
    private $workflowProcess;

    public function __construct(TranscriptCaptionRepositoryInterface $transcriptCaptionRepository, CaptionRepositoryInterface $captionRepository, mediaRepositoryInterface $mediaRepository, WorkflowProcessRepositoryInterface $workflowProcess)
    {
        $this->transcriptCaptionRepository = $transcriptCaptionRepository;
        $this->captionRepository = $captionRepository;
        $this->mediaRepository = $mediaRepository;
        $this->workflowProcess = $workflowProcess;
        $this->user = User::first();
    }

    /**
     * @request  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * URL: api/v1/media/captions/original/create
     */
    public function genearteTranscription(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                    'auto' => 'required',
                    'minDuration' => 'required|numeric',
                    'maxDuration' => 'required|numeric|gte:minDuration',
                    'frameGap' => 'required|numeric',
                    'maxLinePerSubtitle' => 'required|numeric',
                    'maxCharsPerLine' => 'required|numeric',
                    'maxCharsPerSecond' => 'required|numeric',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            if ($media->languageId == null) {
                if ($request->languageId == null) {
                    throw new CustomException(config('constant.SOURCE_LANG_NOT_EXIST_MESSAGE'));
                }
                $this->mediaRepository->updateMedia([
                    'indicator' => [
                        'languageId' => $request->languageId,
                        'updatedBy' => auth()->user()->userId
                    ],
                    'where' => [
                        'mediaId' => $media->mediaId,
                    ]
                ]);
                $media->languageId = $request->languageId;

            }
            $mediaTranscript = $this->transcriptCaptionRepository->getTranscript([
                'where' => [
                    'mediaId' => $media->mediaId,
                ],
                'languageId' => $media->languageId,
                'orderBy' => [
                    'mediaTranscriptId' => 'desc'
                ]
            ]);
            if ($mediaTranscript === null) {
                //get workflowId current state
                $workflowCurrentStateId = $this->workflowProcess->getWorkflowProcess([
                'where' => [
                    'workflowId' => $media->workflowId,
                    'currentStateStatus' => 'unassigned',
                ],
                'select' => ['currentStateId']
                ]);

                $mediaTranscript = $this->transcriptCaptionRepository->addTranscript([
                    'indicator' => [
                        'mediaId' => $media->mediaId,
                        'languageId' => $media->languageId,
                        'minDuration' => $request->minDuration,
                        'maxDuration' => $request->maxDuration,
                        'frameGap' => $request->frameGap,
                        'maxLinePerSubtitle' => $request->maxLinePerSubtitle,
                        'maxCharsPerLine' => $request->maxCharsPerLine,
                        'maxCharsPerSecond' => $request->maxCharsPerSecond,
                        'auto' => $request->auto,
                        'workflowId' => $media->workflowId,
                        'workflowStateId' => $workflowCurrentStateId->currentStateId,
                        'transitionStatus' => 'unassigned',
                        'createdBy' => auth()->user()->userId,
                    ]
                ]);
            }

            if ($request->auto != 1 && $request->hasFile('textFile')) {
                $fileValidator = Validator::make(
                    [
                        'fileExtention' => strtolower($request->textFile->getClientOriginalExtension()),
                    ],    
                    [
                        'fileExtention' => 'in:txt,srt,vtt',
                    ]
                );

                if ($fileValidator->fails()) {
                    throw new CustomException($fileValidator->errors());
                }

                if ($request->textFile->getSize() ===0) {
                    throw new CustomException('Selected file is empty. Please select a file with some content.');
                }
                
                //get workflowId current state
                $workflowCurrentStateId = $this->workflowProcess->getWorkflowProcess([
                    'where' => [
                        'workflowId' => $media->workflowId,
                        'currentStateStatus' => 'inprocess',
                    ],
                    'select' => ['currentStateId']
                ]);

                $path = "/projects/" . $media->projectId . "/media/" . $media->mediaId . "/transcript/" . $mediaTranscript->mediaTranscriptId;
                $fileHandler = new FileHandler();
                $fileExtension = $request->textFile->getClientOriginalExtension();
                $textType = $fileExtension == "txt" ? "plain" : "time";
                $file = "$textType-text-file." . $fileExtension;
                $filePath = "$path/$file";
                $uploadTextFiles = $fileHandler->upload($request->textFile, $path, $file);
                if ($uploadTextFiles['success'] == false) {
                    if ($uploadTextFiles['customException'] == true) {
                        throw new CustomException($uploadTextFiles['message']);
                    } else {
                        throw new Exception($uploadTextFiles['message']);
                    }
                }
                $this->transcriptCaptionRepository->updateTranscript([
                    'indicator' => [
                        $textType . 'TextFile' => $uploadTextFiles['data']['filePath'],
                        'textBreakBy' => $request->textBreakBy ?? null,
                        'workflowStateId' => $workflowCurrentStateId->currentStateId,
                        'linguistId' => auth()->user()->userId,
                        'transitionStatus' => 'inprocess',
                    ],
                    'where' => [
                        'mediaTranscriptId' => $mediaTranscript->mediaTranscriptId,
                    ],
                ]);
                $timeTextHandler = TimeTextFactory::getObject($fileExtension);
                $rslt = $timeTextHandler->import([
                    'fileContent' => Storage::get($uploadTextFiles['data']['filePath']),
                    'guideline' => [
                        'minDuration' => $request->minDuration,
                        'maxDuration' => $request->maxDuration,
                        'frameGap' => $request->frameGap,
                        'maxLinePerSubtitle' => $request->maxLinePerSubtitle,
                        'maxCharsPerLine' => $request->maxCharsPerLine,
                        'maxCharsPerSecond' => $request->maxCharsPerSecond,
                        'textBreakBy' => $request->textBreakBy,
                    ],
                    'mediaDuration' => $media->duration,
                    'sentenceBreaker' => $mediaTranscript->language->sentenceBreaker,
                ]);
                $this->captionRepository->deleteCaption(['mediaTranscriptId' => $mediaTranscript->mediaTranscriptId]);
                foreach ($rslt as $key => $value) {
                    $rslt[$key]['mediaTranscriptId'] = $mediaTranscript->mediaTranscriptId;
                    $rslt[$key]['sourceText'] = $value['text'];
                    $rslt[$key]['createdBy'] = $mediaTranscript->createdBy;
                    $rslt[$key]['createdAt'] = date("Y-m-d H:i:s");
                    unset($rslt[$key]['text']);
                }
                $this->captionRepository->insertCaption(['indicator' => $rslt]);
            } elseif ($request->auto == 1) {
                $this->captionRepository->deleteCaption(['mediaTranscriptId' => $mediaTranscript->mediaTranscriptId]);
                $provider = $request->provider ?? "Google";
                $rslt = $this->autoTranscription([
                    'media' => $media,
                    'mediaTranscript' => $mediaTranscript,
                    'videoPathFFMPeg' => storage_path("app/" . $media->videoPath),
                    'provider' => $provider,
                    'clientIp' => $request->ip(),
                    'callingUrl' => $request->fullUrl(),
                ]);
                if ($rslt['success'] == false) {
                    if ($rslt['customException'] == true) {
                        throw new CustomException($rslt['message']);
                    } else {
                        throw new Exception($rslt['message']);
                    }
                } else {
                    //update here
                    $this->transcriptCaptionRepository->updateTranscript([
                        'indicator' => [
                            'autoTranscribeStatus' => 1,
                        ],
                        'where' => [
                            'mediaTranscriptId' => $mediaTranscript->mediaTranscriptId,
                        ],
                    ]);
                }
            }
        } catch (CustomException $e) {
            DB::rollback();
            if (isset($filePath)) {
                Storage::delete($filePath);
            }
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            if (isset($filePath)) {
                Storage::delete($filePath);
            }
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/captions/original',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => $e->getFile() . ' on ' . $e->getLine() . ' msg: ' . $e->getMessage(),
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.TRANSCRIPT_LIST_GENERATE_MESSAGE'),
        ]);
    }

    public function autoTranscription($param)
    {
        /*
         * Step 1: Video to audio extraction
         * Step 2: Upload audio to gcloud storage
         * Step 3: Start auto transcription process in provider
         */
        try {
            $uploadJobData = array (
                //'projectId' => $param['media']->projectId,
                'mediaId' => $param['media']->mediaId,
                'mediaTranscriptId' => $param['mediaTranscript']->mediaTranscriptId,
                /*'mediaName' => $param['media']->name,
                'audioPath' => $param['media']->audioPath,
                'mediaCloudUrl' => $param['mediaTranscript']->mediaCloudUrl,*/
                'provider' => $param['provider'],
                'GOOGLE_APPLICATION_CREDENTIALS' => env('GOOGLE_APPLICATION_CREDENTIALS'),
                'GOOGLE_STORAGE_BUCKET' => env('GOOGLE_STORAGE_BUCKET'),
            );

            $startTranscriptionJobData = array (
                'mediaId' => $param['media']->mediaId,
                'mediaTranscriptId' => $param['mediaTranscript']->mediaTranscriptId,
                'userId' => auth()->user()->userId,
                'provider' => $param['provider'],
                'GOOGLE_APPLICATION_CREDENTIALS' => env('GOOGLE_APPLICATION_CREDENTIALS'),
                'GOOGLE_SPEECH_KEY' => env('GOOGLE_SPEECH_KEY'),
                'notify' => array (
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'mediaName' => $param['media']->name
                )
            );

            //Chaining jobs to run in sequence
            UploadFileToCloudStorage::withChain([
                new StartTranscription($startTranscriptionJobData)
            ])->dispatch($uploadJobData);
        } catch (CustomException $e) {
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "customException" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }

        return [
            "success" => true,
        ];
    }

    /**
     * Get auto trancription called by queue
     */
    public function getAutoTranscription($param)
    {
        try {
            $mediaTranscript = MediaTranscript::where(['transcriptionProcessCode' => $param['transcriptionProcessCode']])->first();
            if ($mediaTranscript == null) {
                throw new Exception(config('constant.TRANSCRIPT_ALREADY_PROCESSED_MESSAGE'));
            }
            /** Create provider object */
            $provider = TranscriptionFactory::getObject($param['provider']);
            $rslt = $provider->getTranscription([
                'GOOGLE_SPEECH_KEY' => $param['GOOGLE_SPEECH_KEY'],
                'transcriptionProcessCode' => $param['transcriptionProcessCode'],
                'guideLine' => [
                    'maxLinePerSubtitle' => $mediaTranscript->maxLinePerSubtitle,
                    'maxCharsPerLine' => $mediaTranscript->maxCharsPerLine,
                    'minDuration' => $mediaTranscript->minDuration,
                    'maxDuration' => $mediaTranscript->maxDuration,
                    'maxCharsPerSecond' => $mediaTranscript->maxCharsPerSecond,
                    'frameGap' => $mediaTranscript->frameGap
                ],
            ]);

            if ($rslt['success'] == false) {
                if ($rslt['customException'] == true) {
                    GetTranscription::dispatch([
                        'GOOGLE_SPEECH_KEY' => $param['GOOGLE_SPEECH_KEY'],
                        'provider' => $param['provider'],
                        'transcriptionProcessCode' => $param['transcriptionProcessCode'],
                        'mediaId' => $param['mediaId'],
                        'userId' => $param['userId'],
                        'notify' => $param['notify']
                    ])->delay(now()->addSeconds(5));
                    throw new CustomException($rslt['message']);
                } else {
                    throw new Exception($rslt['message']);
                }
            }
            foreach ($rslt['data']['captions'] as $key => $value) {
                $rslt['data']['captions'][$key]['mediaTranscriptId'] = $mediaTranscript->mediaTranscriptId;
                $rslt['data']['captions'][$key]['createdBy'] = $mediaTranscript->createdBy;
                $rslt['data']['captions'][$key]['createdAt'] = date("Y-m-d H:i:s");
            }
            DB::beginTransaction();
            $this->captionRepository->insertCaption(['indicator' => $rslt['data']['captions']]);

            $this->transcriptCaptionRepository->updateTranscript([
                'indicator' => [
                    'autoTranscribeStatus' => 2,
                ],
                'where' => [
                    'mediaTranscriptId' => $mediaTranscript->mediaTranscriptId,
                ],
            ]);

            //Broadcast event and send notify user about completed auto transcription.
            event(new AutoTranscriptionGenerated($param));
            // tpac: Third party api call
            $tpac = $rslt['data']['rtrnParam'];
            $tpac['provider'] = $param['provider'];
            $tpac['mediaTranscriptId'] = $mediaTranscript->mediaTranscriptId;
            $tpac['clientIp'] = $param['clientIp'] ?? null;
            $tpac['callingUrl'] = $param['callingUrl'] ?? null;
            /**
             * Save third part api call request and respponse
             */
            ThirdPartyApiCall::create($tpac);
        } catch (CustomException $e) {
            DB::rollBack();
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            $this->user->notify(new NotifyException([
                'url' => 'Queue',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return [
                "success" => false,
                "customException" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ];
        }
        DB::commit();
        return [
            "success" => true,
            "messsage" => "Transcription created",
        ];
    }

    /**
     * Get list of transcription
     *
     * URL: api/v1/media/captions/original/list
     */
    public function transcriptionList(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            $whereGroupByLast = !$request->history ? ' mediaId = ' . $media->mediaId . ' AND languageId = ' . $media->languageId : "";
            $data['list'] = $this->transcriptCaptionRepository->listTranscript([
                'userId' => auth()->user()->userId,
                'where' => [
                    ['mediaId', '=', $media->mediaId],
                    ['languageId', '=', $media->languageId],
                ],
                'whereGroupByLast' => $whereGroupByLast,
                'orderBy' => [
                    'mediaTranscriptId' => 'desc'
                ]
            ]);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/captions/original/list',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        return response()->json([
            "success" => true,
            "message" => "Transcription list generated",
            "data" => $data,
        ]);
    }

    /**
     * Get original captions
     * URL: api/v1/media/captions/original
     */
    public function captions(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            $data['captions'] = $this->captionRepository->listCaption([
                'where' => [
                    'mediaId' => $media->mediaId,
                    'languageId' => $media->languageId,
                ],
            ]);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/captions/original',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }

        return response()->json([
            "success" => true,
            "data" => $data,
        ]);
    }

    /**
     * Update list of transcription
     *
     * URL: api/v1/media/captions/original/update
     */
    public function transcriptionListUpdate(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                    'minDuration' => 'required|numeric',
                    'maxDuration' => 'required|numeric|gte:minDuration',
                    'frameGap' => 'required|numeric',
                    'maxLinePerSubtitle' => 'required|numeric',
                    'maxCharsPerLine' => 'required|numeric',
                    'maxCharsPerSecond' => 'required|numeric',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $mediaTranscript = $this->getMediaTranscript($request->mediaId);
            $indicator = $request->all();
            if (isset($indicator['linguistId'])) {
                if (count(Hashids::decode($indicator['linguistId'])) === 0) {
                    throw new CustomException(config('constant.USER_ID_INCORRECT_MESSAGE'));
                }
                $indicator['linguistId'] = Hashids::decode($indicator['linguistId'])[0];
            }

            $indicator['updatedBy'] = auth()->user()->userId;
            unset($indicator['mediaId']);
            DB::beginTransaction();
            $mediaTranscript = $this->transcriptCaptionRepository->updateTranscript([
                'where' => [
                    'mediaTranscriptId' => $mediaTranscript->mediaTranscriptId,
                ],
                'indicator' => $indicator,
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/captions/original/update',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }
        DB::commit();
        return response()->json([
            "success" => true,
            "message" => $msg ?? "",
            "data" => $mediaTranscript,
        ]);
    }


    /**
     * Mark transcription as complete
     *
     * URL: api/v1/media/captions/original/complete
     */
    public function markAsComplete(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                    'transitionStatus' => 'required'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $mediaTranscript = $this->getMediaTranscript($request->mediaId);
            $indicator = array();
            $indicator['transitionStatus'] = $request->transitionStatus;
            $indicator['updatedBy'] = auth()->user()->userId;
            DB::beginTransaction();
            $mediaTranscript = $this->transcriptCaptionRepository->updateTranscript([
                'where' => [
                    'mediaTranscriptId' => $mediaTranscript->mediaTranscriptId,
                ],
                'indicator' => $indicator,
            ]);

            $msg = config('constant.TRANSCRIPT_MARK_COMPLETED_MESSAGE');
        } catch (CustomException $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/captions/original/complete',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }
        DB::commit();
        return response()->json([
            "success" => true,
            "message" => $msg ?? ""
        ]);
    }

    /**
     * Mark transcription as approved
     *
     * URL: api/v1/media/captions/original/approved
     */
    public function markAsApproved(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                    'pmApprovalStatus' => 'required'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $mediaTranscript = $this->getMediaTranscript($request->mediaId);
            $indicator = array();
            $indicator['pmApprovalStatus'] = $request->pmApprovalStatus;
            $indicator['updatedBy'] = auth()->user()->userId;
            DB::beginTransaction();
            $mediaTranscript = $this->transcriptCaptionRepository->updateTranscript([
                'where' => [
                    'mediaTranscriptId' => $mediaTranscript->mediaTranscriptId,
                ],
                'indicator' => $indicator,
            ]);

            $msg = config('constant.TRANSCRIPT_APPROVAL_MESSAGE');
        } catch (CustomException $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/captions/original/approved',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }
        DB::commit();
        return response()->json([
            "success" => true,
            "message" => $msg ?? ""
        ]);
    }

    /**
    * Return media transcript.
    * Accepted parameter encoded MediaId
    */
    public function getMediaTranscript($mediaId)
    {
        try {
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => Hashids::decode($mediaId),
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }

            $mediaTranscript = $this->transcriptCaptionRepository->getTranscript([
                'where' => [
                    'mediaId' => $media->mediaId,
                ],
                'languageId' => $media->languageId,
                'orderBy' => [
                    'mediaTranscriptId' => 'desc'
                ]
            ]);

            if ($mediaTranscript == null) {
                throw new CustomException(config('constant.TRANSCRIPT_NOT_EXIST_MESSAGE'));
            }
            return $mediaTranscript;
        } catch (CustomException $customException) {
            throw $customException;
        } catch (Exception $exception) {
            throw $exception;
        }
    }


    /**
     * Delete list of transcription
     *
     * URL: api/v1/media/captions/original/delete
     */
    public function transcriptionListDelete(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $mediaTranscripts = $this->transcriptCaptionRepository->deleteTranscript([
                'mediaId' => $media->mediaId,
                'languageId' => $media->languageId,
            ]);
            if ($mediaTranscripts != null) {
                foreach ($mediaTranscripts as $mediaTranscript) {
                    Storage::deleteDirectory(dirname($media->videoPath) . "/transcript/" . $mediaTranscript->mediaTranscriptId);
                }
            }
        } catch (CustomException $e) {
            DB::rollBack();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/captions/original/delete',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong",
                "errorCode" => '',
            ]);
        }
        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.TRANSCRIPT_LIST_DELETE_MESSAGE'),
        ]);
    }
}
