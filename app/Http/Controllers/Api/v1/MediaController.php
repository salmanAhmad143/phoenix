<?php

namespace App\Http\Controllers\Api\v1;

use App\Jobs\ExportPCMFromMP3;
use App\Jobs\ExtractMP3FromVideo;
use App\Jobs\ExtractPCMFromMedia;
use App\Libraries\LocalFileStream;
use App\Libraries\S3FileStream;
use Log;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Libraries\FFMpegHandler;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use App\Libraries\FileHandler;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Notifications\Exception as NotifyException;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Repositories\Interfaces\ProjectRepositoryInterface;

class MediaController extends Controller
{
    private $projectRepository;
    private $mediaRepository;

    public function __construct(ProjectRepositoryInterface $projectRepository, MediaRepositoryInterface $mediaRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->mediaRepository = $mediaRepository;
        $this->user = User::first();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * URL: api/v1/media
     */
    public function store(Request $request)
    {
        try {
            $extensions = ['MP4', 'MPEG', 'AVI', '3GP', 'WMV', 'MOV', 'FLV', 'WEBM', 'MPG', 'MP2', 'MPE', 'MPV', 'OGG', 'M4P', 'M4V', 'QT', 'SWF', 'AVCHD', 'MP3', 'FLAC', 'AAC', 'AIFF', 'DSD', 'MQA', 'OGG', 'WAV', 'WMA'];
            $validator = Validator::make(
                $request->all(),
                [
                    'projectId' => 'required|string',
                    'mediaFiles' => [function ($attribute, $value, $fail) use ($extensions) {
                        $names = "";
                        if(!empty($value) && count($value)>0){
                            foreach ($value as $mediaName) {
                                $mediaExtension = explode(".", $mediaName);
                                if (!in_array(strtoupper(last($mediaExtension)), $extensions)) {
                                    $names .= $mediaName . ", ";
                                }
                            }
                            if ($names) {
                                $names = rtrim($names, ", ");
                                $fail($names . ' is invalid');
                            }
                        } else {
                            $fail(config('constant.MEDIA_REQURIED_MESSAGE'));
                        }
                    }]
                ]
            );
            if ($validator->fails()) {
                throw new CustomException($validator->errors());
            }
            if (count(Hashids::decode($request->projectId)) === 0) {
                throw new CustomException(config('constant.PROJECT_ID_INCORRECT_MESSAGE'));
            }
            $project = $this->projectRepository->getProject([
                'userId' => auth()->user()->userId,
                'where' => [
                    'projectId' => Hashids::decode($request->input("projectId")),
                    'status' => 1
                ]
            ]);
            if ($project == null) {
                throw new CustomException(config('constant.PROJECT_NOT_EXIST_MESSAGE'));
            }
            DB::beginTransaction();
            $mediaIds = [];
            $mediaExist = [];
            foreach ($request->mediaFiles as $mediaName) {
                $media = $this->mediaRepository->getMedia([
                    'where' => [
                        'name' => $mediaName,
                        'projectId' => Hashids::decode($request->input("projectId")),
                    ]
                ]);
                // $media = null;
                // $mediaName = "4.37.mp4";
                $videoPath = $media->videoPath ?? null;
                if ($media == null && count($mediaExist) == 0) {
                    $media = $this->mediaRepository->addMedia([
                        'indicator' => [
                            'name' => $mediaName,
                            'workflowId' => $project->workflowId,
                            'projectId' => $project->projectId,
                            'createdBy' => auth()->user()->userId,
                        ]
                    ]);
                } elseif ($videoPath) {
                    $mediaExist[] = $mediaName;
                }
                if (isset($media->mediaId)) {
                    $mediaIds[] = $media->id;
                }
            }
            if (count($mediaExist) > 0) {
                throw new CustomException(config('constant.MEDIA_DUPLICATE_MESSAGE'));
            }
        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "mediaExist" => $mediaExist ?? [],
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media',
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
            "mediaIds" => $mediaIds ?? [],
        ]);
    }

    /**
     * URL: api/v1/media/details
     */
    public function show(Request $request)
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
            } else {
                $media->workflowName = $media->workflow->name ?? null;
                unset($media->workflow);
                $language = $media->language->language ?? null;
                unset($media->language);
                $media->language = $language;
                $media->videoPath = env('APP_URL') . "/api/v1/media?mediaId=" . $media->id;
                $media->videoImagePath = env('APP_URL') . "/api/v1/media-image?mediaId=" . $media->id;
                $media->pcmPath = env('APP_URL') . "/api/v1/media-pcm?mediaId=" . $media->id;
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/details',
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
            "data" => [
                'media' => $media,
            ],
        ]);
    }

    /**
     * URL: api/v1/media/update
     */
    public function update(Request $request)
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
            $validation = $this->mediaRepository->getMedia([
                'where' => [
                    ['name', '=', $request->name],
                    ['projectId', '=', $media->projectId],
                    ['mediaId', '!=', $media->mediaId],
                ]
            ]);
            if ($validation != null) {
                throw new CustomException(config('constant.MEDIA_DUPLICATE_MESSAGE'));
            }
            // $validator = Validator::make(
            //     $request->all(),
            //     [
            //         'name' => 'string|min:3|max:100|unique:media,name,' . $media->mediaId . ',mediaId',
            //     ]
            // );
            // if ($validator->fails()) {
            //     throw new CustomException($validator->errors());
            // }

            DB::beginTransaction();

            //TOASK: Wether these fields are mandatory
            $indicator = [];
            if ($request->name) {
                $indicator['name'] = $request->name;
            }
            if ($request->workflowId) {
                $indicator['workflowId'] = $request->workflowId;
            }
            if ($request->languageId) {
                $indicator['languageId'] = $request->languageId;
            }
            if (count($indicator) > 0) {
                $indicator['updatedBy'] =  auth()->user()->userId;
                $this->mediaRepository->updateMedia([
                    'indicator' => $indicator,
                    'where' => [
                        'mediaId' => $media->mediaId,
                    ]
                ]);
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/update',
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
            "message" => config('constant.MEDIA_UPDATE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/media/delete
     */
    public function destroy(Request $request)
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
            DB::beginTransaction();
            $videoPath = $media->videoPath;
            $this->mediaRepository->deleteMedia([
                'mediaId' => $media->mediaId,
            ]);

            if (!empty($videoPath)) {
                if (env('UPLOAD_STORAGE') === 'S3')  {
                    Storage::disk('s3')->deleteDirectory(dirname($videoPath));
                } else {
                    Storage::deleteDirectory(dirname($videoPath));
                }
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/delete',
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
            "message" => config('constant.MEDIA_DELETE_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/media
     */
    public function getMedia(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("mediaId"))) === 0) {
                throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
            }
            $media = $this->mediaRepository->getMedia([
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                    'status' => 1
                ]
            ]);
            if ($media->count() == 0) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }

            if (env('UPLOAD_STORAGE') === 'S3') {
                $stream = new S3FileStream($media->videoPath); 
                return $stream->output();        
            } else {
                $stream = new LocalFileStream($media->videoPath);
                $stream->start();
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/details',
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
    }

    public function getMediaPcm(Request $request)
    {
        try {
            if (count(Hashids::decode($request->mediaId)) === 0) {
                throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
            }

            $media = $this->mediaRepository->getMedia([
                'where' => [
                    'mediaId' => Hashids::decode($request->mediaId),
                    'status' => 1
                ]
            ]);
            if ($media->count() == 0) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }

            $videoPath = $media->videoPath;
            $pcmPath = substr_replace($videoPath , 'pcm', strrpos($videoPath , '.') +1);
            
            if (env('UPLOAD_STORAGE') === 'S3') {
                $stream = new S3FileStream($pcmPath);
                return $stream->output();         
            } else {
                $fileHandler = new FileHandler();
                $fileHandler->getVideo($pcmPath);
            }
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/details',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => "Something went wrong".$e->getMessage(),
                "errorCode" => '',
            ]);
        }
    }

    /**
     * URL: api/v1/media-image
     */
    public function getMediaImage(Request $request)
    {
        try {
            if (count(Hashids::decode($request->input("mediaId"))) === 0) {
                throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
            }
            $media = $this->mediaRepository->getMedia([
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                    'status' => 1
                ]
            ]);
            if ($media->count() == 0) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            $fileHandler = new FileHandler();
            $response = $fileHandler->getImage($media->videoImagePath);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media/details',
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
        return $response;
    }

    /**
     * URL: api/v1/media/extract-info
     */
    public function extractInfo(Request $request)
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
            DB::beginTransaction();
            $media = $this->mediaRepository->getMedia([
                'where' => [
                    'mediaId' => Hashids::decode($request->input("mediaId")),
                ]
            ]);
            if ($media == null) {
                throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
            }
            $mediaDirectory = "/projects/" . $media->projectId . "/media/" . $media->mediaId;
            $mediaPath =  $mediaDirectory . "/" . $media->name;
            $FFMpegHandler = new FFMpegHandler();
            $mediaInfo = $FFMpegHandler->getMediaInfo(storage_path("app" . $mediaPath));
            if ($mediaInfo['success'] == false) {
                if ($mediaInfo['customException'] == true) {
                    if ($media->delete()) {
                        //Deleting media from db and directory.
                        Storage::deleteDirectory(dirname($mediaPath));
                        DB::commit();
                    }                    
                    return $mediaInfo;
                } else {
                    throw new Exception($mediaInfo['message']);
                }
            }
            $mediaImage = $FFMpegHandler->getThumbnail(storage_path("app" . $mediaPath));
            if ($mediaImage['success'] == false) {
                if ($mediaImage['customException'] == true) {
                    throw new CustomException($mediaImage['message']);
                } else {
                    throw new Exception($mediaImage['message']);
                }
            }
            $this->mediaRepository->updateMedia([
                'indicator' => [
                    'videoPath' => $mediaPath,
                    'videoImagePath' => $mediaDirectory . "/" . $mediaImage['data']['videoImageName'],
                    'duration' => $mediaInfo['data']['duration'] ?? null,
                    'videoFrameRate' => $mediaInfo['data']['frameRate'] ?? null,
                    'videoSampleRate' => $mediaInfo['data']['sampleRate'] ?? null, //TODO: change column name to audioSampleRate as it's for audio
                    'videoBitRate' => $mediaInfo['data']['bitRate'] ?? null,
                    'audioChannels' => $mediaInfo['data']['audioChannels'],
                    'streamDetails' => $mediaInfo['data']['streamDetails']
                ],
                'where' => [
                    'mediaId' => $media->mediaId,
                ]
            ]);

            //setting job to extract MP3 from audio and converting it to PCM.
            $extractionPCMJobData = array (
                'projectId' => $media->projectId,
                'mediaId' => $media->mediaId,
                'videoPathFFMPeg' => storage_path("app" . $mediaPath),
                'audioPath' => $media->audioPath,
                'notify' => array (
                    'userId' => auth()->user()->id,
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email
                )
            );
            ExtractPCMFromMedia::dispatch($extractionPCMJobData);
        } catch (CustomException $e) {
            DB::rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            DB::rollback();
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media',
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'msg' => $e->getMessage(),
            ]));
            return response()->json([
                "success" => false,
                "message" => 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage(),
                "errorCode" => '',
            ]);
        }

        DB::commit();
        return response()->json([
            "success" => true,
            "message" => config('constant.MEDIA_UPLOAD_MESSAGE'),
        ]);
    }

    /**
     * URL: api/v1/tus
     */
    public function tusUpload(Request $request)
    {
        try {
            $path = "";
            $server   = new \TusPhp\Tus\Server('redis');
            if ($request->isMethod('POST')) {
                $mediaId = Hashids::decode($server->getRequest()->extractMeta('mediaId'));
                if (count($mediaId) === 0) {
                    throw new CustomException(config('constant.MEDIA_ID_INCORRECT_MESSAGE'));
                }
                $media = $this->mediaRepository->getMedia([
                    'where' => [
                        'mediaId' => $mediaId,
                        'status' => 1
                    ]
                ]);
                if ($media == null) {
                    throw new CustomException(config('constant.MEDIA_NOT_EXIST_MESSAGE'));
                }
                $directory = "/projects/" . $media->projectId . "/media/" . $media->mediaId;
                Storage::makeDirectory($directory);
                $path = storage_path('app/' . $directory);
            }

            $server
                ->setApiPath('/tus') // tus server endpoint.
                ->setUploadDir($path); // uploads dir.
            $response = $server->serve();
            $response->send();
            exit(0);
        } catch (CustomException $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ]);
        } catch (Exception $e) {
            $this->user->notify(new NotifyException([
                'url' => 'api/v1/media',
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
    }
}
