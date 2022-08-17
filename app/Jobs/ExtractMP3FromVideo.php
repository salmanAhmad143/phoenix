<?php

namespace App\Jobs;

use App\Exceptions\CustomException;
use App\Mail\MediaReady;
use App\Repositories\MediaRepository;
use App\Libraries\FFMpegHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ExtractMP3FromVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mediaId = null;
    protected $audioPath = null;
    protected $videoPath = null;
    protected $userId = null;
    protected $mediaTranscriptId = null;
    protected $notify = null;

    public $timeout = 0; //To allow job to run for infinite time

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->mediaId = $param['mediaId'];
        $this->audioPath = $param['audioPath'];
        $this->videoPath = $param['videoPathFFMPeg'];
        $this->userId = $param['userId'];
        $this->notify = $param['notify'];
       // $this->mediaTranscriptId = $param['mediaTranscriptId'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            //extracting audio from video if audioPath is not set or audio is not present in location
           // if (empty($this->audioPath) || !File::exists($this->audioPath)) {
                $ffmpegHandler = new FFMpegHandler();
                $convertToAudio = $ffmpegHandler->convertToAudioMP3($this->videoPath);
                if ($convertToAudio['success'] == false) {
                    if ($convertToAudio['customException'] == true) {
                        throw new CustomException($convertToAudio['message']);
                    } else {
                        throw new \Exception($convertToAudio['message']);
                    }
                }

                //updating media to processed
                $mediaRepository = new MediaRepository();
                $mediaRepository->updateMedia([
                    'indicator' => [
                        'mediaProcessingStatus' => 1,
                    ],
                    'where' => [
                        'mediaId' => $this->mediaId,
                    ]
                ]);
                
                //Send notification email when media is ready.
                $mediaParts = pathinfo($this->videoPath);
                $this->notify['mediaName'] = $mediaParts['filename'];
                Mail::to($this->notify['email'])->send(new MediaReady($this->notify));

                /*$mediaRepository = new MediaRepository();
                $mediaRepository->updateMedia([
                    'indicator' => [
                        'audioPath' => $convertToAudio['data']['audioPath'],
                        'updatedBy' => $this->userId//auth()->user()->userId, //TODO: check if values are correct or not
                    ],
                    'where' => [
                        'mediaId' => $this->mediaId,
                    ]
                ]);*/
                //$this->param['media']->audioPath = $convertToAudio['data']['audioPath'];
            //}
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
       /* $transcriptCaptionRepository = new TranscriptCaptionRepository();
        $transcriptCaptionRepository->updateTranscript([
            'indicator' => [
                'autoTranscribeStatus' => 0,
            ],
            'where' => [
                'mediaTranscriptId' => $this->mediaTranscriptId,
            ],
        ]);*/
        $exceptionString = 'ERROR: While MP3 extraction - File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage();
        Mail::raw($exceptionString, function ($message) {
            $message->to(env('EXCEPTION_EMAIL'));
            $message->subject('Phoenix - Exception');
        });
    }
}
