<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\v1\Factories\TimeTextFactory;
use App\Http\Controllers\Api\v1\Factories\TranslationFactory;
use App\Models\User;
use App\Models\ThirdPartyApiCall;
use App\Repositories\Interfaces\TranscriptCaptionRepositoryInterface;
use App\Repositories\Interfaces\TranslateCaptionRepositoryInterface;
use App\Repositories\Interfaces\CaptionRepositoryInterface;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Libraries\FileHandler;
use App\Exceptions\CustomException;
use App\Notifications\Exception as NotifyException;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use App\Repositories\Interfaces\WorkflowProcessRepositoryInterface;
use App\Jobs\StartTranscription;
use App\Jobs\genearteAutoTranslation;

class TranslateCaptionController extends Controller
{
    private $translateCaptionRepository;
    private $transcriptCaptionRepository;
    private $captionRepository;
    private $mediaRepository;
    private $workflowProcess;

    public function __construct(TranslateCaptionRepositoryInterface $translateCaptionRepository, TranscriptCaptionRepositoryInterface $transcriptCaptionRepository, CaptionRepositoryInterface $captionRepository, MediaRepositoryInterface $mediaRepository,WorkflowProcessRepositoryInterface $workflowProcess)
    {
        $this->transcriptCaptionRepository = $transcriptCaptionRepository;
        $this->translateCaptionRepository = $translateCaptionRepository;
        $this->captionRepository = $captionRepository;
        $this->mediaRepository = $mediaRepository;
        $this->workflowProcess = $workflowProcess;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/media/captions/translation/create
     */
    public function genearteTranslation(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("mediaId"))) === 0) {
                throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
            } else {
                $mediaId = Hashids::decode($request->input("mediaId"));
            }

            $validator = Validator::make(
                $request->all(),
                [
                    'mediaId' => 'required|string',
                    'languageId' => 'required|array',
                    'languageId.*' => 'unique:media_transcript,languageId,NULL,id,mediaId,'.$mediaId[0],
                    'auto' => 'required',
                    'textFile' => 'mimes:txt',
                    'minDuration' => 'required|numeric',
                    'maxDuration' => 'required|numeric|gte:minDuration',
                    'frameGap' => 'required|numeric',
                    'maxLinePerSubtitle' => 'required|numeric',
                    'maxCharsPerLine' => 'required|numeric',
                    'maxCharsPerSecond' => 'required|numeric',
                ], $this->customMessage($request)
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $media = $this->mediaRepository->getMedia([
                'userId' => auth()->user()->userId,
                'where' => [
                    'mediaId' => $mediaId,
                    'status' => 1
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            if ($media->languageId == null) {
                throw new CustomException(config('constant.SOURCE_LANG_NOT_EXIST_MESSAGE'));
            }

            DB::beginTransaction();
            foreach ($request->languageId as $languageId) {
                $languageId = intval(str_replace('"', '', $languageId));
                $mediaTranslate = $this->translateCaptionRepository->getTranslate([
                    'where' => [
                        'mediaId' => $media->mediaId,
                        'languageId' => $languageId,
                    ],
                    'languageId' => $media->languageId,
                    'orderBy' => [
                        'mediaTranscriptId' => 'desc'
                    ]
                ]);
                if ($mediaTranslate === null) {
                    //get workflowId current state
                    $workflowCurrentStateId = $this->workflowProcess->getWorkflowProcess([
                    'where' => [
                        'workflowId' => $media->project->workFlowTranslationId,
                        'currentStateStatus' => 'unassigned',
                    ],
                    'select' => ['currentStateId']
                    ]);
                    $mediaTranslate = $this->translateCaptionRepository->addTranslate([
                        'indicator' => [
                            'mediaId' => $media->mediaId,
                            'languageId' => $languageId,
                            'minDuration' => $request->minDuration,
                            'maxDuration' => $request->maxDuration,
                            'frameGap' => $request->frameGap,
                            'maxLinePerSubtitle' => $request->maxLinePerSubtitle,
                            'maxCharsPerLine' => $request->maxCharsPerLine,                        
                            'maxCharsPerSecond' => $request->maxCharsPerSecond,
                            'auto' => $request->auto,
                            'workflowId' => $media->project->workFlowTranslationId,
                            'workflowStateId' => $workflowCurrentStateId->currentStateId,
                            'transitionStatus' => 'unassigned',
                            'createdBy' => auth()->user()->userId,
                        ]
                    ]);
                }

                $originalTranscript = $this->transcriptCaptionRepository->getTranscript([
                    'where' => [
                        'mediaId' => $media->mediaId,
                    ],
                    'languageId' => $media->languageId,
                    'orderBy' => [
                        'mediaTranscriptId' => 'desc'
                    ]
                ]);

                if ($request->auto != 1 && $request->hasFile('textFile')) {
                    if ($originalTranscript != null) {
                        throw new CustomException(config('constant.TRANSCRIPT_ORIGINAL_EXIST_FOR_TRANSLATION_MESSAGE'));
                    }
                    $path = "/projects/" . $media->projectId . "/media/" . $media->mediaId . "/transcript/" . $mediaTranslate->mediaTranscriptId;
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
                    $this->translateCaptionRepository->updateTranslate([
                        'indicator' => [
                            $textType . 'TextFile' => $uploadTextFiles['data']['filePath'],
                            'textBreakBy' => $request->textBreakBy ?? null,
                            'workflowStateId' => 2,
                            'linguistId' => auth()->user()->userId,
                            'transitionStatus' => 'inprocess',
                        ],
                        'where' => [
                            'mediaTranscriptId' => $mediaTranslate->mediaTranscriptId,
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
                            'textBreakBy' => $request->textBreakBy ?? null,
                        ],
                        'mediaDuration' => $media->duration,
                        'sentenceBreaker' => $mediaTranslate->language->sentenceBreaker,
                    ]);
                    $this->captionRepository->deleteCaption(['mediaTranscriptId' => $mediaTranslate->mediaTranscriptId]);
                    foreach ($rslt as $key => $value) {
                        $rslt[$key]['mediaTranscriptId'] = $mediaTranslate->mediaTranscriptId;
                        $rslt[$key]['targetText'] = $value['text'];
                        $rslt[$key]['createdBy'] = $mediaTranslate->createdBy;
                        $rslt[$key]['createdAt'] = date("Y-m-d H:i:s");
                        unset($rslt[$key]['text']);
                    }
                    $this->captionRepository->insertCaption(['indicator' => $rslt]);
                } elseif ($request->auto == 1) {
                    if ($originalTranscript === null) {
                        throw new CustomException(config('constant.TRANSCRIPT_ORIGINAL_NOT_EXIST_MESSAGE'));
                    } else {
                        if (count($originalTranscript->mediaCaption) == 0) {
                            throw new CustomException(config('constant.TRANSCRIPT_ORIGINAL_NOT_EXIST_MESSAGE'));
                        } elseif ($originalTranscript->pmApprovalStatus == 0) {
                            throw new CustomException(config('constant.ORIGINAL_CAPTIONS_NOT_APPROVED_MESSAGE'));
                        }
                    }
                    $provider = $request->provider ?? "Google";
                    $rslt = $this->autoTranslation([
                        'media' => $media,
                        'originalTranscriptId' => $originalTranscript->mediaTranscriptId,
                        'mediaTranscript' => $mediaTranslate,
                        'provider' => $provider,
                        'clientIp' => $request->ip(),
                        'callingUrl' => $request->fullUrl(),
                        'workflowId' => $media->project->workFlowTranslationId
                    ]);
                    if ($rslt['success'] == false) {
                        if ($rslt['customException'] == true) {
                            throw new CustomException($rslt['message']);
                        } else {
                            throw new Exception($rslt['message']);
                        }
                    } else {
                        //update here
                        $this->translateCaptionRepository->updateTranslate([
                            'indicator' => [
                                'autoTranscribeStatus' => 1,
                            ],
                            'where' => [
                                'mediaTranscriptId' => $mediaTranslate->mediaTranscriptId,
                            ],
                        ]);
                    }
                } else {
                    if ($originalTranscript != null) {
                        if ($originalTranscript->pmApprovalStatus == 0) {
                            throw new CustomException(config('constant.ORIGINAL_CAPTIONS_NOT_APPROVED_MESSAGE'));
                        }
                        // $captions = $this->captionRepository->listCaption(['where' => ['mediaTranscriptId' => $originalTranscript->mediaTranscriptId]]);
                        $captions = $originalTranscript->mediaCaption;
                        if ($captions != null) {
                            $captionArray = [];
                            foreach ($captions->toArray() as $caption) {
                                $caption['mediaTranscriptId'] = $mediaTranslate->mediaTranscriptId;
                                unset($caption['mediaCaptionId']);
                                $caption['createdBy'] = auth()->user()->userId;
                                $caption['createdAt'] = Carbon::now();
                                if (isset($caption['id'])) {
                                    unset($caption['id']);
                                }
                                $captionArray[] = $caption;
                            }

                            $this->captionRepository->deleteCaption(['mediaTranscriptId' => $mediaTranslate->mediaTranscriptId]);
                            $this->captionRepository->insertCaption(['indicator' => $captionArray]);
                        }
                    }
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
                'url' => "api/v1/media/captions/translation/create",
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong ".$e->getMessage(),
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.TRANSLATION_LIST_GENERATE_MESSAGE'),
        ]);
    }

    public function autoTranslation($param)
    {
        try {

            $uploadJobData = array (
                'mediaId' => $param['media']->mediaId,
                'originalTranscriptId' => $param['originalTranscriptId'],
                'mediaTranscriptId' => $param['mediaTranscript']->mediaTranscriptId,
                'targetLanguage' => $param['mediaTranscript']->language->languageCode,
                'workflowId' => $param['workflowId'],
                'provider' => $param['provider'],
                'userId' => auth()->user()->userId,
                'notify' => array (
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'mediaName' => $param['media']->name
                )
            );
            genearteAutoTranslation::dispatch($uploadJobData);
            
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
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }

        return [
            "success" => true,
        ];
    }

    /**
     * Get list of translation
     *
     * URL: api/v1/media/captions/translation/list
     */
    public function translationList(Request $request)
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
            $whereGroupByLast = !$request->history ? ' mediaId = ' . $media->mediaId . ' AND languageId != ' . $media->languageId : "";
            $where = [
                ['mediaId', '=', $media->mediaId],
                ['languageId', '!=', $media->languageId],
            ];
            if ($request->languageId) {
                $where[] = ['languageId', '=', $request->languageId];
            }
            $data['list'] = $this->translateCaptionRepository->listTranslate([
                'userId' => auth()->user()->userId,
                'where' => $where,
                'whereGroupByLast' => $whereGroupByLast,
                'orderBy' => [
                    'mediaTranscriptId' => 'desc'
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
            "data" => $data,
        ]);
    }

    /**
     * Get translated captions
     * URL: api/v1/media/captions/translation
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
                    'languageId' => $request->languageId,
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
                'url' => 'api/v1/media/captions/translation',
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
     * Update list of translation
     *
     * URL: api/v1/media/captions/translation/update
     */
    public function translationListUpdate(Request $request)
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

            $mediaTranslate = $this->getMediaTranscript($request->mediaId, $request->languageId);
            $indicator = $request->all();
            if (isset($indicator['linguistId'])) {
                if (count(Hashids::decode($indicator['linguistId'])) === 0) {
                    throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
                }
                $indicator['linguistId'] = Hashids::decode($indicator['linguistId'])[0];
            }
            $indicator['updatedBy'] = auth()->user()->userId;
            unset($indicator['mediaId']);
            DB::beginTransaction();
            $mediaTranscript = $this->translateCaptionRepository->updateTranslate([
                'where' => [
                    'mediaTranscriptId' => $mediaTranslate->mediaTranscriptId,
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
                'url' => 'api/v1/media/captions/translation/update',
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
     * Mark translation as complete
     *
     * URL: api/v1/media/captions/translation/complete
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

            $mediaTranslate = $this->getMediaTranscript($request->mediaId, $request->languageId);
            $indicator = array();
            $indicator['transitionStatus'] = $request->transitionStatus;
            $indicator['updatedBy'] = auth()->user()->userId;
            DB::beginTransaction();
            $mediaTranscript = $this->translateCaptionRepository->updateTranslate([
                'where' => [
                    'mediaTranscriptId' => $mediaTranslate->mediaTranscriptId,
                ],
                'indicator' => $indicator,
            ]);
            
            $msg = config('constant.TRANSLATION_MARK_COMPLETED_MESSAGE');
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
                'url' => 'api/v1/media/captions/translation/complete',
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
    * Return media transcript data.
    * Accepted encoded MediaId and LanguageId parameter
    */
    public function getMediaTranscript($mediaId, $languageId)
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
            if ($media->languageId == $languageId) {
                throw new CustomException(config('constant.LANGUAGE_INCORRECT_MESSAGE'));
            }

            $mediaTranslate = $this->translateCaptionRepository->getTranslate([
                'where' => [
                    'mediaId' => $media->mediaId,
                    'languageId' => $languageId,
                ],
                'languageId' => $media->languageId,
                'orderBy' => [
                    'mediaTranscriptId' => 'desc'
                ]
            ]);

            if ($mediaTranslate == null) {
                throw new CustomException(config('constant.TRANSLATION_NOT_EXIST_MESSAGE'));
            }
            return $mediaTranslate;
        } catch (CustomException $customException) {
            throw $customException;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Delete list of translation
     *
     * URL: api/v1/media/captions/translation/delete
     */
    public function translationListDelete(Request $request)
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
            if ($media->languageId == $request->languageId) {
                throw new CustomException(config('constant.LANGUAGE_INCORRECT_MESSAGE'));
            }
            DB::beginTransaction();
            $mediaTranscripts = $this->translateCaptionRepository->deleteTranslate([
                'mediaId' => $media->mediaId,
                'languageId' => $request->languageId,
            ]);
            if ($mediaTranscripts !== null) {
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
                'url' => 'api/v1/media/captions/translation/delete',
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
            "message" => config('constant.TRANSLATION_LIST_DELETE_MESSAGE'),
        ]);
    }

    //Custom messages for add translation validation.
    public function customMessage($request)
    {
        if($request->languageId) {
            foreach ($request->languageId as $key => $value) {
                $customMessages['languageId.' . $key . '.unique'] = ++$key.config('constant.TRANSLATION_ALREADY_CREATED_MESSAGE');
            }
            return $customMessages;
        }
    }
}
