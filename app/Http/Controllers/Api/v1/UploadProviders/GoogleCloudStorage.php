<?php


namespace App\Http\Controllers\Api\v1\UploadProviders;


use App\Exceptions\CustomException;
use Google\Cloud\Storage\StorageClient;

class GoogleCloudStorage
{
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
            $config = [
                'keyFilePath' => '/Users/bug/Local_Web/Google_GCP_Key/code-phoenix-dev-20873b668164.json'
                //'projectId' => 'code-phoenix-dev',
            ];
            $storage = new StorageClient($config);
            $file = fopen($source, 'r');
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
}
