<?php

namespace App\Jobs;

use App\Exceptions\CustomException;
use App\Libraries\FFMpegHandler;
use App\Repositories\MediaRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ExtractFLACFromVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mediaId = null;
    protected $audioPath = null;
    protected $videoPath = null;
    protected $userId = null;

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
            if (empty($this->audioPath) || !File::exists($this->audioPath)) {
                $ffmpegHandler = new FFMpegHandler();
                $convertToAudio = $ffmpegHandler->convertToAudioFlac($this->videoPath);
                if ($convertToAudio['success'] == false) {
                    if ($convertToAudio['customException'] == true) {
                        throw new CustomException($convertToAudio['message']);
                    } else {
                        throw new \Exception($convertToAudio['message']);
                    }
                }

                $flacPath = $convertToAudio['data']['audioPath'];
                if (env('UPLOAD_STORAGE') === 'S3') {
                    $bucketName = env('AWS_BUCKET');
                    $flacPath = str_replace(storage_path("app/"), "", $flacPath);
                    $flacPath = "s3://{$bucketName}/{$flacPath}";           
                }

                $mediaRepository = new MediaRepository();
                $mediaRepository->updateMedia([
                    'indicator' => [
                        'audioPath' => $flacPath,
                        'updatedBy' => $this->userId
                    ],
                    'where' => [
                        'mediaId' => $this->mediaId,
                    ]
                ]);
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
    }

    public function failed(\Exception $e)
    {
        // Save transcription url to MediaTranscript table
        // $transcriptCaptionRepository = new TranscriptCaptionRepository();
        // $transcriptCaptionRepository->updateTranscript([
        //     'indicator' => [
        //         'autoTranscribeStatus' => 0,
        //     ],
        //     'where' => [
        //         'mediaTranscriptId' => $this->mediaTranscriptId,
        //     ],
        // ]);
        $exceptionString = 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage();
        Mail::raw($exceptionString, function ($message) {
            $message->to(env('EXCEPTION_EMAIL'));
            $message->subject('Phoenix - Exception');
        });
    }
}
