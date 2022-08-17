<?php

namespace App\Jobs;

use App\Exceptions\CustomException;
use App\Http\Controllers\Api\v1\Factories\TranscriptionFactory;
use App\Models\ThirdPartyApiCall;
use App\Repositories\MediaRepository;
use App\Repositories\TranscriptCaptionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class UploadFileToCloudStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //protected $projectId = null;
    protected $mediaId = null;
    protected $mediaTranscriptId = null;
    protected $mediaRepositry = null;
    protected $transcriptCaptionRepository = null;
    /*protected $mediaName = null;
    protected $audioPath = null;
    protected $mediaCloudUrl = null;*/
    protected $provider = null;
    private $GOOGLE_APPLICATION_CREDENTIALS = null;
    private $GOOGLE_STORAGE_BUCKET = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        //$this->projectId = $param['projectId'];
        $this->mediaId = $param['mediaId'];
        $this->mediaTranscriptId = $param['mediaTranscriptId'];
        $this->mediaRepositry = new MediaRepository();
        $this->transcriptCaptionRepository = new TranscriptCaptionRepository();
        /*$this->mediaName = $param['mediaName'];
        $this->audioPath = $param['audioPath'];
        $this->mediaCloudUrl = $param['mediaCloudUrl'];*/
        $this->provider = $param['provider'];
        $this->GOOGLE_APPLICATION_CREDENTIALS = $param['GOOGLE_APPLICATION_CREDENTIALS'];
        $this->GOOGLE_STORAGE_BUCKET = $param['GOOGLE_STORAGE_BUCKET'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            //Getting details of media and media transcript and call startTranscription function in provider
            $media = $this->mediaRepositry->getMedia([
                'where' => [
                    'mediaId' => $this->mediaId,
                    'status' => 1
                ]
            ]);

            $mediaTranscript = $this->transcriptCaptionRepository->getTranscript([
                'where' => [
                    'mediaTranscriptId' => $this->mediaTranscriptId,
                ]
            ]);

            if (empty($mediaTranscript->mediaCloudUrl)) {
                $uploadProvider = TranscriptionFactory::getObject($this->provider);
                //Send media to provider to get transcription url
                $rslt = $uploadProvider->pushMedia([
                    'projectId' => $media->projectId,
                    'audioPath' => $media->audioPath,
                    'mediaName' => $media->mediaName,
                    'GOOGLE_APPLICATION_CREDENTIALS' => $this->GOOGLE_APPLICATION_CREDENTIALS,
                    'GOOGLE_STORAGE_BUCKET' => $this->GOOGLE_STORAGE_BUCKET
                ]);

                if ($rslt['success'] == false) {
                    if ($rslt['customException'] == true) {
                        throw new CustomException($rslt['message']);
                    } else {
                        throw new \Exception($rslt['message']);
                    }
                }
                // Save transcription url to MediaTranscript table
                $this->transcriptCaptionRepository->updateTranscript([
                    'indicator' => [
                        'mediaCloudUrl' => $rslt['data']['gcUrl'],
                    ],
                    'where' => [
                        'mediaTranscriptId' => $this->mediaTranscriptId,
                    ],
                ]);

                // Save third part api call request and respponse
                $tpac = $rslt['data']['rtrnParam'];
                $tpac['provider'] = $this->provider;
                $tpac['mediaTranscriptId'] = $this->mediaTranscriptId;
                //TODO: check if these two field are relevant or not
                //$tpac['clientIp'] = $param['clientIp'];
                //$tpac['callingUrl'] = $param['callingUrl'];
                ThirdPartyApiCall::create($tpac);

                /*$startTranscriptionJobData = array (
                    'mediaId' => $this->mediaId,
                    'mediaTranscriptId' => $this->mediaTranscriptId,
                    'provider' => $this->provider
                );*/

                //StartTranscription::dispatch($startTranscriptionJobData);
            }
        } catch (CustomException $e) {
            $this->failed($e);
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (\Exception $e) {
            $this->failed($e);
            return [
                "success" => false,
                "customException" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }

        /*return [
            "success" => true,
        ];*/
    }

    public function failed(\Exception $e)
    {
        // Save transcription url to MediaTranscript table
        $this->transcriptCaptionRepository->updateTranscript([
            'indicator' => [
                'autoTranscribeStatus' => 0,
            ],
            'where' => [
                'mediaTranscriptId' => $this->mediaTranscriptId,
            ],
        ]);
        $exceptionString = 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage();
        Mail::raw($exceptionString, function ($message) {
            $message->to(env('EXCEPTION_EMAIL'));
            $message->subject('Phoenix - Exception');
        });
    }
}
