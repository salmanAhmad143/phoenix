<?php

namespace App\Http\Controllers\Api\v1\TranscriptionProviders;

use App\Repositories\MediaRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;
use App\Exceptions\CustomException;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Speech\V1p1beta1\SpeechClient;
use Google\Cloud\Speech\V1p1beta1\RecognitionAudio;
use Google\Cloud\Speech\V1p1beta1\RecognitionConfig;
use App\Http\Controllers\Api\v1\Interfaces\Transcription;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Storage;

// use Google\Cloud\Speech\V1\SpeechClient;
// use Google\Cloud\Speech\V1\RecognitionConfig;
// use Google\Cloud\Speech\V1\StreamingRecognitionConfig;
// use Google\Cloud\Speech\V1\StreamingRecognizeRequest;
// use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

class GoogleTranscription implements Transcription
{
    public $adapter = null;
    public $disk = null;

    public function __construct()
    {
        /**
         * Requirments:
         * google cloud sdk from https://cloud.google.com/sdk/docs/downloads-interactive#windows,
         * Google cloude sdk requires Python to be installed,
         * GOOGLE_APPLICATION_CREDENTIALS in environment variable.
         */
        $this->disk        = Storage::disk("s3");
        $this->adapter     = $this->disk->getAdapter();
    }

    public function pushMedia($param)
    {
        try {
            /**
             * resource: https://cloud.google.com/php
             * Upload audio to google cloud and get google cloud url of uploaded audio in response
             */
            $bucketName = $param['GOOGLE_STORAGE_BUCKET'];
            $source = $param['audioPath'];
            $objectName = $param['projectId'] . "_" . basename($source);

            $rtrnParam = [
                'provider' => "Google",
                'request' => "file content",
            ];

            $rtrnParam['requestTime'] = date("Y-m-d H:i:s");
            //TODO: update this for better option then saving file path here.
            $config = [
                'keyFilePath' => $param['GOOGLE_APPLICATION_CREDENTIALS']
            ];

            $storage = new StorageClient($config);
            $stream = [];
            if (env('UPLOAD_STORAGE') === 'S3') {
                $this->adapter->getClient()->registerStreamWrapper();
                // Create a stream to allow seeking from S3
                $stream =[
                    's3' => [
                        'seekable' => true,
                    ],
                ];
            }

            $context = stream_context_create($stream);
            // Open a stream in read-only mode
            if (!($file = fopen($source, 'rb', false, $context))) {
                throw new Exception('Could not open stream for reading export [' . $source . ']');
            }

            $bucket = $storage->bucket($bucketName);
            $rtrnParam['response'] = $bucket->upload($file, [
                'name' => $objectName
            ]);
            $rtrnParam['responseTime'] = date("Y-m-d H:i:s");
            // $object = $bucket->object($objectName);
            $rslt = @json_decode($rtrnParam['response'], true);
            if (isset($rslt['error'])) {
                throw new Exception($rtrnParam['response']);
            }
            unset($rtrnParam['response']);
            $data['gcUrl'] = "gs://" . $bucketName . "/" . $objectName;
            $data['rtrnParam'] = $rtrnParam;
        } catch (CustomException $e) {
            return [
                "success" => false,
                "customException" => true,
                "message" => "GoogleTranscription->pushMedia() " . $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "customException" => false,
                "message" => "GoogleTranscription->pushMedia() " . $e->getMessage(),
                "errorCode" => '',
            ];
        }

        return [
            "success" => true,
            "messsage" => "File uploaded successfully",
            "data" => $data,
        ];
    }

    public function startTranscription($param)
    {
        try {
            $uri = $param['gcUrl'];

            $encoding = AudioEncoding::FLAC;//8;//AudioEncoding:://'MP3';
            $sampleRateHertz = $param['videoSampleRate'];
            $languageCode = $param['languageCode'];
            $enableAutomaticPunctuation = true;
            $enableWordTimeOffsets = true;
            $model = $param['model'];

            $audio = (new RecognitionAudio())->setUri($uri);

            $config = (new RecognitionConfig())
                ->setEncoding($encoding)
                ->setSampleRateHertz($sampleRateHertz)
                ->setLanguageCode($languageCode)
                ->setModel($model)
                ->setEnableWordTimeOffsets($enableWordTimeOffsets)
                ->setEnableAutomaticPunctuation($enableAutomaticPunctuation)
                ->setAudioChannelCount($param['audioChannels'])
                //->setEnableSpeakerDiarization(true)
                ->setUseEnhanced(true);

            //TODO: update this for better option then saving file path here.
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $param['GOOGLE_APPLICATION_CREDENTIALS']);

            $client = new SpeechClient();
            $operation = $client->longRunningRecognize($config, $audio);
            $operationName = $operation->getName();
            $client->close();

            //checkin if there is any error in operation
            if (!empty($operation->getError())) {
                throw new Exception($operation->getError());
            }

            if (!empty($operationName)) {

            }

            $rtrnParam['request'] = 'NEED TO FIX THIS to save request details';
            $rtrnParam['url'] = null;
            $rtrnParam['requestTime'] = date("Y-m-d H:i:s");
            $rtrnParam['response'] = json_encode(array('name' => $operationName));
            $rtrnParam['responseTime'] = date("Y-m-d H:i:s");
            // $rtrnParam['response'] = '{"name":"260280641155742165"}';
            $data = json_decode($rtrnParam['response'], true);
            if (!isset($data['name'])) {
                throw new Exception($rtrnParam['response']);
            }
            $data['rtrnParam'] = $rtrnParam;
        } catch (CustomException $e) {
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "customException" => false,
                "message" => "GoogleTranscription->startTranscription() " . $e->getMessage() . ' FILE: ' . $e->getFile() . ' ON LINE ' . $e->getLine(),
                "errorCode" => '',
            ];
        }

        return [
            "success" => true,
            "messsage" => "File uploaded successfully",
            "data" => $data,
        ];
    }

    public function getTranscription($param)
    {
        try {

            /*putenv('GOOGLE_APPLICATION_CREDENTIALS=/Users/bug/Local_Web/Google_GCP_Key/code-phoenix-dev-20873b668164.json');

            $client = new SpeechClient();
            $operation = $client->resumeOperation($param['transcriptionProcessCode']);
            $operation->pollUntilComplete();

            if ($operation->operationSucceeded()) {
                $response = $operation->getResult();
                Log::info(json_encode($response));
                die('dd');

                // each result is for a consecutive portion of the audio. iterate
                // through them to get the transcripts for the entire audio file.
                foreach ($response->getResults() as $result) {
                    $alternatives = $result->getAlternatives();
                    $mostLikely = $alternatives[0];
                    $transcript = $mostLikely->getTranscript();
                    $confidence = $mostLikely->getConfidence();
                    printf('Transcript: %s' . PHP_EOL, $transcript);
                    printf('Confidence: %s' . PHP_EOL, $confidence);
                }
            } else {
                print_r($operation->getError());
            }

            $client->close();
die('MM');*/

            //TODO: update this for better option then saving key here.
            $rtrnParam['url'] = "https://speech.googleapis.com/v1p1beta1/operations/" . $param['transcriptionProcessCode'] . "?key=" . $param['GOOGLE_SPEECH_KEY'];
            $rtrnParam['requestTime'] = date("Y-m-d H:i:s");
            $rtrnParam['response'] = Curl::to($rtrnParam['url'])->get();

            $rtrnParam['responseTime'] = date("Y-m-d H:i:s");
            $rslt = json_decode($rtrnParam['response'], true);
            if (!isset($rslt['name'])) {
                throw new Exception($rtrnParam['response']);
            }
            if (!isset($rslt['response'])) {
                throw new CustomException("Transcription is processing");
            }
            $rtrnParam['results'] = json_encode($rslt['response']['results']);
            $data = $rslt['response'];
            $data['rtrnParam'] = $rtrnParam;
            //Log::info(json_encode($rslt));
            //print_r($rslt['response']['results']);
            //die('ddd');
            $gCaptionGen = new GoogleCaptionGenerator();
            $gCaptionGen->setGuideline($param['guideLine']);
            $data['captions'] = $gCaptionGen->generateCaption(['results' => $rslt['response']['results']]);
            //$data['captions'] = $this->generateCaption(['results' => $rslt['response']['results'], 'guideLine' => $param['guideLine']]);
        } catch (CustomException $e) {
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (Exception $e) {
            return [
                "success" => false,
                "customException" => false,
                "message" => "GoogleTranscription->getTranscription() " . $e->getMessage(),
                "errorCode" => '',
            ];
        }

        return [
            "success" => true,
            "messsage" => "File uploaded successfully",
            "data" => $data,
        ];
    }
}
