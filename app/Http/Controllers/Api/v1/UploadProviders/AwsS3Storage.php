<?php

namespace App\Http\Controllers\Api\v1\UploadProviders;

use App\Exceptions\MultipartUploadException;
use App\Exceptions\CustomException;
use Aws\Exception\AwsException;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;

class AwsS3Storage
{
    private $s3Client;

    public function __construct()
    {
        $this->s3Client = new S3Client([
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
        ]);
    }
 
    /**
     * Accept sourcePath and targetPath files path. 
     * sourcePath => where all local files stored.
     * targetPath => where the files will save on AWS.
     */
    public function pushMedia($params)
    {
        try {
            $this->scanDirectory($params);
        } catch (MultipartUploadException $e) {
            $params = $e->getState()->getId();
            $this->s3Client->abortMultipartUpload($params);
            throw new CustomException($e);
        } catch (Exception $e) {
            throw $e;
        }

        return [
            "success" => true,
            "messsage" => "File uploaded successfully",
        ];
    }

    /**
     * Scaned all the directories and 
     * Loop through array of all files.
    */
    public function scanDirectory($params) {
        $sourcePath = array_diff(scandir($params['sourcePath']), ['.', '..']);
        if (count($sourcePath)>0) {
            foreach($sourcePath as $fileName) {
                $params['fileName'] = $fileName;
                $this->uploadFile($params);
            }
        } else {
            throw new MultipartUploadException("There are no file in directory.");
        }
    }

    /**
     * Upload files on AWS server in chunk.
     */
    public function uploadFile($params) {
        $source = $params['sourcePath'] . $params['fileName'];
        $target = $params['targetPath'] . $params['fileName'];
        $uploader = new MultipartUploader($this->s3Client, $source, [
            'bucket' => env('AWS_BUCKET'),
            'key' => $target,
            'part_size' => 5*1024*1024
        ]);
        $uploader->upload();
    }

    /**
     * Return AWS file signed path
     * Accept two parameters. Bucket file path.
     * Time to availabe signed link.
     */
    public function awsFilePath($path, $duration = 5) {
        $command = $this->s3Client->getCommand('GetObject', [
            'Bucket' => env('AWS_BUCKET'),
            'Key' => ltrim($path, "/")
        ]);
        $request = $this->s3Client->createPresignedRequest($command, "+{$duration} minutes");
        $file = (string)$request->getUri();
        return $file;
    }
}
