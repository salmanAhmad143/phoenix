<?php

namespace App\Jobs;

use App\Exceptions\CustomException;
use App\Jobs\ExtractFLACFromVideo;
use App\Jobs\UploadFileToS3Storage;
use App\Libraries\FFMpegHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Helpers\common;

class ExtractPCMFromMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId = null;
    protected $mediaId = null;
    protected $videoPath = null;
    protected $audioPath = null;
    protected $notify = null;

    public $timeout = 0; //To allow job to run for infinite time
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->mediaId = $param['mediaId'];
        $this->projectId = $param['projectId'];
        $this->videoPath = $param['videoPathFFMPeg'];
        $this->audioPath = $param['audioPath'];
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
            $commom = new common();
            if ($this->notify['userId']) {
                $idParam['id'] = $this->notify['userId'];
                $idParam['errorMeg'] = "constant.USER_ID_INCORRECT_MESSAGE";
                $userId = $commom->getDecodeId($idParam);
                $this->notify['userId'] = $userId;
            }
            $mediaParts = pathinfo($this->videoPath);
            $pcmPath = dirname($this->videoPath) . DIRECTORY_SEPARATOR . $mediaParts['basename'];
            shell_exec('node ' . env('WEB_AUDIO_JS_PATH') . ' ' . $pcmPath);
            
            $extractionJobData = array (
                'mediaId' => $this->mediaId,
                'videoPathFFMPeg' => $this->videoPath,
                'audioPath' => $this->audioPath,
                'userId' => $this->notify['userId']
            );
            
            //Running jobs in sequence.
            ExtractFLACFromVideo::withChain([
                new UploadFileToS3Storage([
                    'projectId' => $this->projectId,
                    'mediaId' => $this->mediaId,
                    'mediaName' => $mediaParts['filename'],
                    'notify' => $this->notify
                ])
            ])->dispatch($extractionJobData);
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
        $exceptionString = 'ERROR: While PCM extraction - File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage();
        Mail::raw($exceptionString, function ($message) {
            $message->to(env('EXCEPTION_EMAIL'));
            $message->subject('Phoenix - Exception');
        });
    }
}
