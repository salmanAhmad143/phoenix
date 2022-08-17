<?php

namespace App\Libraries;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\v1\Factories\UploadFactory;

class FileHandler extends Controller
{

    /*
     * Request: Request will be in array includes 'sourcePath', 'targetPath', 'fileName', 'fileNameRandom'
     * Response: fileName, basePath, filePath, fileExtension
     */
    public function upload($source, $target, $fileName = null, $fileNameRandom = null)
    {
        try {
            $data['directory'] = $target;
            if ($fileNameRandom != null) {
                $data['filePath'] = Storage::putFile($target, $source);
            } else {
                if ($fileName == null) {
                    $file = $source->getClientOriginalName();
                    $fileParts = pathinfo($file);
                    $fileName = $fileParts['basename'];
                }
                $data['filePath'] = Storage::putFileAs($target, $source, $fileName);
            }
            $data['filePath'] = "/" . $data['filePath'];
            $mediaParts = pathinfo($data['filePath']);
            $data['baseName'] = $mediaParts['basename'];
            $data['fileName'] = $mediaParts['filename'];
            $data['fileExtension'] = $mediaParts['extension'];
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
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }

        return [
            "success" => true,
            "messsage" => "Media uploaded successfully",
            "data" => $data,
        ];
    }

    public function getImage($path)
    {
        try {
            if (env('UPLOAD_STORAGE') === 'S3') {
                if (File::exists(storage_path("app/" . $path))) {
                    $file = storage_path("app/" . $path);
                    $response = response()->file($file);
                } else if(Storage::disk('s3')->has($path)) {
                    /** Creating provider object**/
                    $provider = UploadFactory::getObject('AwsS3');
                    $file = $provider->awsFilePath($path, 30);
                    header('Content-type: image/jpeg');
                    $response = readfile($file);
                } else {
                    $file = public_path("images/" . 'speaker_icon.svg');
                    $response = response()->file($file);
                }
            } else {
                if (File::exists(storage_path("app/" . $path))) {
                    $file = storage_path("app/" . $path);
                } else {
                    $file = public_path("images/" . 'speaker_icon.svg');
                }
                $response = response()->file($file);
            }
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
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }
        return $response;
    }

    public function getVideo($path)
    {
        try {
            header('Access-Control-Allow-Origin: *');
            $file = storage_path("app/" . $path);

            $mime = Storage::mimeType($path);
            $size = filesize($file);
            $length = $size;
            $start = 0;
            $end = $size - 1;

            header(sprintf('Content-type: %s', $mime));
            header('Accept-Ranges: bytes');

            if (isset($_SERVER['HTTP_RANGE'])) {
                $c_start = $start;
                $c_end = $end;

                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

                if (strpos($range, ',') !== false) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header(sprintf('Content-Range: bytes %d-%d/%d', $start, $end, $size));

                    exit;
                }

                if ($range == '-') {
                    $c_start = $size - substr($range, 1);
                } else {
                    $range  = explode('-', $range);
                    $c_start = $range[0];
                    $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                }

                $c_end = ($c_end > $end) ? $end : $c_end;

                if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header(sprintf('Content-Range: bytes %d-%d/%d', $start, $end, $size));

                    exit;
                }

                header('HTTP/1.1 206 Partial Content');

                $start = $c_start;
                $end = $c_end;
                $length = $end - $start + 1;
            }

            header("Content-Range: bytes $start-$end/$size");
            header(sprintf('Content-Length: %d', $length));

            $fh = fopen($file, 'rb');
            $buffer = 1024 * 8;

            fseek($fh, $start);

            while (true) {
                if (ftell($fh) >= $end) {
                    break;
                }
                set_time_limit(0);
                echo fread($fh, $buffer);
                flush();
            }
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
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }
    }
}
