<?php

namespace App\Jobs;

use App\Exceptions\CustomException;
use App\Mail\MediaReady;
use App\Repositories\MediaRepository;
use App\Repositories\TranscriptCaptionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Vinkla\Hashids\Facades\Hashids;

class ExportPCMFromMP3 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $videoPath = null;
    protected $mediaId = null;
    protected $pcmFilePath = null;
    protected $notify = null;
    protected $flamingoPCMPath = null;

    public function __construct($param)
    {
        $this->videoPath = $param['videoPathFFMPeg'];
        $this->mediaId = $param['mediaId'];
        $this->pcmFilePath = $param['PHOENIX_PCM_FILE_PATH'];
        $this->notify = $param['notify'];
        $this->flamingoPCMPath = $param['FLAMINGO_PCM_PATH'];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $mediaParts = pathinfo($this->videoPath);
            $mediaName = $mediaParts['filename'];
            $audioPath = dirname($this->videoPath) . "/$mediaName.mp3";

            if(!File::isDirectory($this->pcmFilePath)){
                File::makeDirectory($this->pcmFilePath, 0777, true, true);
            }

            //echo $audioPath;
            $node = shell_exec('node ' . env('WEB_AUDIO_JS_PATH') . ' ' . $audioPath . ' ' . Hashids::encode($this->mediaId));
            //$ffmpegVideo->save($audioFormat, $audioPath);

            //moving pcm file in pcm folder and creating symlink link in flamingo from phoenix
            $mv = 'mv ' . dirname($this->videoPath) . DIRECTORY_SEPARATOR . $mediaName . '_' . Hashids::encode($this->mediaId) . '.pcm ' . $this->pcmFilePath . DIRECTORY_SEPARATOR . $mediaName . '_' . Hashids::encode($this->mediaId) . '.pcm';
            //echo $mv;
            shell_exec($mv);
            $ln = 'ln -s ' . $this->pcmFilePath . ' ' . $this->flamingoPCMPath;
            //echo $ln;
            shell_exec($ln);
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

            //TODO:Send email for status update
            /*Mail::raw('Media Rea- ' . $mediaName, function ($message) {
                $message->to($this->notify['email']);
                $message->subject('Media Ready - ');
            });*/
            $this->notify['mediaName'] = $mediaName;
             Mail::to($this->notify['email'])->send(new MediaReady($this->notify));


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
        /*$transcriptCaptionRepository = new TranscriptCaptionRepository();
        $transcriptCaptionRepository->updateTranscript([
            'indicator' => [
                'autoTranscribeStatus' => 0,
            ],
            'where' => [
                'mediaTranscriptId' => $this->mediaTranscriptId,
            ],
        ]);*/
        $exceptionString = 'ERROR: In export PCM from MP3 - FileFile: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage();
        Mail::raw($exceptionString, function ($message) {
            $message->to(env('EXCEPTION_EMAIL'));
            $message->subject('Phoenix - Exception');
        });
    }
}
