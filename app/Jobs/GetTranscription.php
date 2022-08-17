<?php

namespace App\Jobs;

use App\Exceptions\CustomException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\Api\v1\TranscriptCaptionController;
use App\Repositories\TranscriptCaptionRepository;
use App\Repositories\CaptionRepository;
use App\Repositories\MediaRepository;
use App\Repositories\WorkflowProcessRepository;
use Illuminate\Support\Facades\Mail;

class GetTranscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $param;
    
    public $timeout = 0; //To allow job to run for infinite time
    public $tries = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $captionController = new TranscriptCaptionController(new TranscriptCaptionRepository, new CaptionRepository, new MediaRepository, new WorkflowProcessRepository);
            $captionController->getAutoTranscription($this->param);
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
        $exceptionString = 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage();
        Mail::raw($exceptionString, function ($message) {
            $message->to(env('EXCEPTION_EMAIL'));
            $message->subject('Phoenix - Exception');
        });
    }
}
