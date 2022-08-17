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

class StartTranscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mediaId = null;
    protected $mediaTranscriptId = null;
    protected $userId = null;
    protected $provider = null;
    protected $mediaRepositry = null;
    protected $transcriptCaptionRepository = null;
    private $GOOGLE_APPLICATION_CREDENTIALS = null;
    private $GOOGLE_SPEECH_KEY = null;
    private $notify = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->mediaId = $param['mediaId'];
        $this->mediaTranscriptId = $param['mediaTranscriptId'];
        $this->userId = $param['userId'];
        $this->provider = $param['provider'];
        $this->mediaRepositry = new MediaRepository();
        $this->transcriptCaptionRepository = new TranscriptCaptionRepository();
        $this->GOOGLE_APPLICATION_CREDENTIALS = $param['GOOGLE_APPLICATION_CREDENTIALS'];
        $this->GOOGLE_SPEECH_KEY = $param['GOOGLE_SPEECH_KEY'];
        $this->notify = $param['notify'];
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

            if (!empty($mediaTranscript->mediaCloudUrl)) {
                $transcriptProvider = TranscriptionFactory::getObject($this->provider);
                $rslt = $transcriptProvider->startTranscription([
                    'GOOGLE_APPLICATION_CREDENTIALS' => $this->GOOGLE_APPLICATION_CREDENTIALS,
                    'projectId' => $media->projectId,
                    'audioChannels' => $media->audioChannels,
                    'audioPath' => $media->audioPath,
                    'gcUrl' => $mediaTranscript->mediaCloudUrl,
                    'videoSampleRate' => $media->videoSampleRate,
                    'languageCode' => $mediaTranscript->language->languageCode,
                    'serviceProvider' => $mediaTranscript->language->serviceProvider,
                    'model' => $mediaTranscript->language->model,
                    'guideLine' => [
                        'minDuration' => $mediaTranscript->minDuration,
                        'maxDuration' => $mediaTranscript->maxDuration,
                        'frameGap' => $mediaTranscript->frameGap,
                        'maxLinePerSubtitle' => $mediaTranscript->maxLinePerSubtitle,
                        'maxCharsPerLine' => $mediaTranscript->maxCharsPerLine,
                        'maxCharsPerSecond' => $mediaTranscript->maxCharsPerSecond,
                        'subtitleSyncAccuracy' => $mediaTranscript->subtitleSyncAccuracy,
                        'textBreakBy' => $mediaTranscript->textBreakBy,
                    ]
                ]);

                if ($rslt['success'] == false) {
                    if ($rslt['customException'] == true) {
                        throw new CustomException($rslt['message']);
                    } else {
                        throw new \Exception($rslt['message']);
                    }
                }

                $transcriptionProcessCode = $rslt['data']['name'];
                $this->transcriptCaptionRepository->updateTranscript([
                    'indicator' => [
                        'transcriptionProcessCode' => $transcriptionProcessCode,
                        'workflowStateId' => 2,
                        'linguistId' => $this->userId,
                        'transitionStatus' => 'inprocess',
                        'autoTranscribeStatus' => 1
                    ],
                    'where' => [
                        'mediaTranscriptId' => $this->mediaTranscriptId,
                    ],
                ]);

                // Save third part api call request and respponse
                $tpac = $rslt['data']['rtrnParam'];
                $tpac['provider'] = $this->provider;
                $tpac['mediaTranscriptId'] = $this->mediaTranscriptId;
                //TODO: check if we can remove these two columns
                //$tpac['clientIp'] = $param['clientIp'];
                //$tpac['callingUrl'] = $param['callingUrl'];
                ThirdPartyApiCall::create($tpac);

                // generate queue to get transcription
                GetTranscription::dispatch([
                    'GOOGLE_SPEECH_KEY' => $this->GOOGLE_SPEECH_KEY,
                    'provider' => $this->provider,
                    'transcriptionProcessCode' => $transcriptionProcessCode,
                    'mediaId' => $this->mediaId,
                    'userId' => $this->userId,
                    'notify' => $this->notify

                ])->delay(now()->addSeconds(5));
            } else {
                throw new CustomException('Media Cloud URL is empty in StartTranscripton Job');
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

      /*  return [
            "success" => true,
        ];*/
    }

    public function failed(\Exception $e)
    {
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
