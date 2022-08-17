<?php

namespace App\Http\Controllers\Api\v1;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Http\Controllers\Api\v1\Factories\TimeTextFactory;
use App\Repositories\Interfaces\CaptionRepositoryInterface;
use App\Repositories\Interfaces\TranscriptCaptionRepositoryInterface;

class CaptionController extends Controller
{
    private $captionRepository;
    private $transcriptCaptionRepository;
    private $mediaRepository;

    public function __construct(CaptionRepositoryInterface $captionRepository, TranscriptCaptionRepositoryInterface $transcriptCaptionRepository, MediaRepositoryInterface $mediaRepository)
    {
        $this->captionRepository = $captionRepository;
        $this->transcriptCaptionRepository = $transcriptCaptionRepository;
        $this->mediaRepository = $mediaRepository;
        $this->user = User::first();
    }

    /**
     * URL: api/v1/caption/create
     */
    public function store(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("mediaId"))) === 0) {
                throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
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
            $validator = Validator::make(
                $request->all(),
                [
                    'languageId' => 'required|int',
                    'start' => 'required|int',
                    'end' => 'required|int',
                    'text' => 'required|string'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }

            $transcription = $this->captionRepository->getTranscriptionId(['mediaId' => $media->mediaId, 'languageId' => $request->languageId]);
            if ($transcription == null) {
                throw new CustomException(config('constant.TRANSCRIPT_NOT_EXIST_MESSAGE'));
            }
            $textColumn = "sourceText";
            if ($media->languageId != $request->languageId) {
                $textColumn = "targetText";
            }
            $caption = $this->captionRepository->addCaption(['indicator' => [
                'mediaTranscriptId' =>  $transcription->mediaTranscriptId,
                'startTime' => $request->start,
                'endTime' => $request->end,
                $textColumn => $request->text,
                'createdBy' => auth()->user()->userId,
            ]]);
            
            //Checking if linguist is assigned then change the status.
            if ($transcription->linguistId) {
                $this->transcriptCaptionRepository->updateTranscript([
                    'where' => [
                        'mediaTranscriptId' => $transcription->mediaTranscriptId
                    ],
                    'indicator' => ['transitionStatus' => 'inprocess'],
                ]);
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/caption/create',
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
            "message" => config('constant.CAPTION_CREATE_MESSAGE'),
            "data" => [
                'mediaCaptionId' => $caption->id
            ]
        ]);
    }

    /**
     * URL: api/v1/caption/update
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'captions' => 'required|array',
                    'captionType' => 'required|in:original,translation'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $textColumn = "sourceText";
            if ($request->captionType == "translation") {
                $textColumn = "targetText";
            }
            foreach ($request->captions as $caption) {
                $mediaCaption = $this->captionRepository->getCaption(['mediaCaptionId' => Hashids::decode($caption['mediaCaptionId'])]);
                if ($mediaCaption == null) {
                    throw new CustomException(config('constant.CAPTION_NOT_EXIST_MESSAGE'));
                }

                $this->captionRepository->updateCaption([
                    'indicator' => [
                        'startTime' => $caption['start'],
                        'endTime' => $caption['end'],
                        $textColumn => $caption['text'],
                        'updatedBy' => auth()->user()->userId,
                    ],
                    'where' => [
                        'mediaCaptionId' => $mediaCaption->mediaCaptionId
                    ],
                ]);
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/caption/update',
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
            "message" => config('constant.CAPTION_UPDATE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/caption/update
     */
    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'mediaCaptionId' => 'required|string'
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            $mediaCaption = $this->captionRepository->getCaption(['mediaCaptionId' => Hashids::decode($request->mediaCaptionId)]);
            if ($mediaCaption == null) {
                throw new CustomException(config('constant.CAPTION_NOT_EXIST_MESSAGE'));
            }
            $this->captionRepository->deleteCaption(['mediaCaptionId' => Hashids::decode($request->mediaCaptionId)]);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/caption/update',
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
            "message" => config('constant.CAPTION_DELETE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/caption/export
     */
    public function export(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("mediaId"))) === 0) {
                throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'languageId' => 'required',
                    'type' => 'required|string'
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
            $captions = $this->captionRepository->listCaption([
                'where' => [
                    'mediaId' => $media->mediaId,
                    'languageId' => $request->input("languageId"),
                ],
            ]);
            if ($captions == null) {
                throw new CustomException(config('constant.CAPTION_NOT_EXIST_MESSAGE'));
            }
            $textColumn = 'targetText';
            if ($request->input("languageId") == $media->languageId) {
                $textColumn = 'sourceText';
            }
            $timeTextHandler = TimeTextFactory::getObject($request->input("type"));
            $rslt = $timeTextHandler->export([
                'captions' => $captions,
                'textColumn' => $textColumn
            ]);
            if ($rslt['success'] == false) {
                throw new CustomException(config('constant.CAPTION_NOT_EXIST_MESSAGE'));
            }
            $fileName = pathinfo($media->name, PATHINFO_FILENAME) . "-" . $media->language->language . "." . $request->input('type');
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/caption/export',
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
        // return response($rslt['data'])->withHeaders([
        //     'Content-Type' => 'text/plain',
        //     'Cache-Control' => 'no-store, no-cache',
        //     'Content-Disposition' => 'attachment; filename=' . $fileName,
        // ]);
        return response()->json([
            "success" => true,
            "data" => [
                'captions' => $rslt['data'],
                'fileName' => $fileName
            ]
        ]);
    }
}
