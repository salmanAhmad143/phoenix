<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Repositories\MediaRepository;
use App\Http\Controllers\Api\v1\Factories\UploadFactory;
use App\Events\MediaWasProcessed;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Mail;

class UploadFileToS3Storage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $projectId = null;
    protected $mediaId = null;
    protected $mediaName = null;
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
        $this->projectId = $param['projectId'];
        $this->mediaId = $param['mediaId'];
        $this->mediaName = $param['mediaName'];
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
            if (env('UPLOAD_STORAGE') === 'S3') {
                /** Creating provider object**/
                $provider = UploadFactory::getObject('AwsS3');
                $targetPath = "projects/{$this->projectId}/media/{$this->mediaId}/";
                $sourcePath = storage_path("app/{$targetPath}/");
                /** Upload all project media on AWS server. */
                $uploadMedia = $provider->pushMedia([
                    'sourcePath' => $sourcePath,
                    'targetPath' => $targetPath
                ]);

                if ($uploadMedia['success']) {
                    Storage::deleteDirectory($targetPath);
                }
            }
            
            //Updating media to processed
            $mediaRepository = new MediaRepository();
            $mediaRepository->updateMedia([
                'indicator' => [
                    'mediaProcessingStatus' => 1,
                ],
                'where' => [
                    'mediaId' => $this->mediaId,
                ]
            ]);

            //Broadcast message file processed to frontend.
            $this->notify['mediaName'] = $this->mediaName;
            $this->notify['projectId'] = Hashids::encode($this->projectId);
            event(new MediaWasProcessed($this->notify));
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
