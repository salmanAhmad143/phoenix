<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exceptions\CustomException;
use App\Repositories\WorkflowProcessRepository;
use App\Repositories\captionRepository;
use App\Repositories\TranslateCaptionRepository;
use Illuminate\Support\Facades\Mail;
use App\Events\AutoTranslationGenerated;

use App\Http\Controllers\Api\v1\Factories\TranslationFactory;

class genearteAutoTranslation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $originalTranscriptId = null;
    protected $mediaTranscriptId = null;
    protected $targetLanguage =null;
    protected $workflowId =null;
    protected $captionRepository =null;
    protected $workflowProcess =null;
    protected $translateCaptionRepository =null;
    protected $provider = null;
    protected $linguistId =null;
    protected $mediaId =null;
    protected $notify =null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->originalTranscriptId = $param['originalTranscriptId'];
        $this->mediaTranscriptId = $param['mediaTranscriptId'];
        $this->targetLanguage = $param['targetLanguage'];
        $this->workflowId = $param['workflowId'];
        $this->notify = $param['notify'];
        $this->mediaId = $param['mediaId'];
        $this->captionRepository = new captionRepository();
        $this->workflowProcess = new WorkflowProcessRepository();
        $this->translateCaptionRepository = new TranslateCaptionRepository();
        $this->provider = $param['provider'];
        $this->linguistId = $param['userId'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $provider = TranslationFactory::getObject($this->provider);
            $this->captionRepository->insertSourceText([
                'where' => ['mediaTranscriptId' => $this->originalTranscriptId],
                'mediaTranscriptId' => $this->mediaTranscriptId,
                'userId' => $this->linguistId,
            ]);
            $originalCaptions = $this->captionRepository->listCaption([
                'where' => ['mediaTranscriptId' => $this->mediaTranscriptId],
            ]);
            foreach ($originalCaptions as $caption) {
                $rslt = $provider->translate([
                    'sourceText' => $caption->sourceText,
                    'targetLanguage' => $this->targetLanguage,
                ]);
                /** Save translation to MediaCaption table */
                $this->captionRepository->updateCaption([
                    'indicator' => ['targetText' => $rslt['text']],
                    'where' => ['mediaCaptionId' => $caption->mediaCaptionId],
                ]);
            }
            //get workflowId current state
            $workflowCurrentStateId = $this->workflowProcess->getWorkflowProcess([
            'where' => [
                'workflowId' => $this->workflowId,
                'currentStateStatus' => 'inprocess',
            ],
            'select' => ['currentStateId']
            ]);
            $this->translateCaptionRepository->updateTranslate([
                'indicator' => [
                    'workflowStateId' => $workflowCurrentStateId->currentStateId,
                    'linguistId' => $this->linguistId,
                    'transitionStatus' => 'inprocess',
                    'autoTranscribeStatus' => 2
                ],
                'where' => [
                    'mediaTranscriptId' => $this->mediaTranscriptId,
                ],
            ]);
            $notificationparam['notify'] = $this->notify;
            $notificationparam['mediaId'] = $this->mediaId;
            $notificationparam['userId'] = $this->linguistId;
            event(new AutoTranslationGenerated($notificationparam));
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
        // update translation autoTranscribeStatus
        $this->translateCaptionRepository->updateTranslate([
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
