<?php


namespace App\Libraries;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Storage;

/**
 * Description of LocalFileStream
 *
 * @author Rana
 * @link http://codesamplez.com/programming/php-html5-video-streaming-tutorial
 */
class LocalFileStream
{
    private $path = "";
    private $stream = "";
    private $mime = null;
    private $buffer = 1048576; //Buffering 1 MB; value is in bytes
    private $start  = -1;
    private $end    = -1;
    private $size   = 0;

    function __construct($filePath)
    {
        $this->path = storage_path("app/" . $filePath);
        $this->mime = Storage::mimeType($filePath);
    }

    /**
     * Open stream
     */
    private function open()
    {
        try {
            if (!($this->stream = fopen($this->path, 'rb'))) {
                throw new CustomException('Could not open stream for reading');
            }
        } catch (CustomException $e) {
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (\Exception $e) {
            return [
                "success" => false,
                "customException" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }
    }

    /**
     * Set proper header to serve the video content
     */
    private function setHeader()
    {
        try {
            ob_get_clean();
            header('Access-Control-Allow-Origin: *');
            //header("Content-Type: video/mp4");
            header("Content-Type: " . $this->mime);
            header("Cache-Control: max-age=2592000, public");
            header("Expires: ".gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
            header("Last-Modified: ".gmdate('D, d M Y H:i:s', @filemtime($this->path)) . ' GMT' );
            $this->start = 0;
            $this->size  = filesize($this->path);
            $this->end   = $this->size - 1;
            header("accept-ranges: bytes");

            if (isset($_SERVER['HTTP_RANGE'])) {
                $c_start = $this->start;
                $c_end = $this->end;
                header("abc: 123");

                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                if (strpos($range, ',') !== false) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $this->start-$this->end/$this->size");
                    //throw new CustomException('Exit here');
                    exit;
                }

                if ($range == '-') {
                    $c_start = $this->size - substr($range, 1);
                } else{
                    $range = explode('-', $range);
                    $c_start = $range[0];

                    $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
                }

                $c_end = ($c_end > $this->end) ? $this->end : $c_end;

                if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $this->start-$this->end/$this->size");
                    //throw new CustomException('Exit here 2');
                    exit;
                }

                $this->start = $c_start;
                $this->end = $c_end;
                $length = $this->end - $this->start + 1;
                fseek($this->stream, $this->start);
                header('HTTP/1.1 206 Partial Content');
                header("Content-Length: ".$length);
                header("Content-Range: bytes $this->start-$this->end/".$this->size);
            } else {
                //header("Content-Length: ".$this->start+$this->buffer);
                //header("Content-Range: bytes $this->start-$this->end/".$this->size);
                header("Content-Length: ".$this->size);
            }
        } catch (CustomException $e) {
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (\Exception $e) {
            return [
                "success" => false,
                "customException" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }
    }

    /**
     * close curretly opened stream
     */
    private function end()
    {
        try {
            fclose($this->stream);
            //throw new CustomException('Exit here 3');
            exit;
        } catch (CustomException $e) {
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        } catch (\Exception $e) {
            return [
                "success" => false,
                "customException" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }
    }

    /**
     * perform the streaming of calculated range
     */
    private function stream()
    {
        try {
            $i = $this->start;
            set_time_limit(0);

            //removed while loop to work with media player
            //while(!feof($this->stream) && $i <= $this->end) {
                $bytesToRead = $this->buffer;
                if(($i+$bytesToRead) > $this->end) {
                    $bytesToRead = $this->end - $i + 1;
                }
                $data = fread($this->stream, $bytesToRead);
                echo $data;
                flush();
                //$i += $bytesToRead;
            //}
        } catch (CustomException $e) {
            return [
                "success" => false,
                "customException" => true,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
            return [
                "success" => false,
                "customException" => false,
                "message" => $e->getMessage(),
                "errorCode" => '',
            ];
        }
    }

    /**
     * Start streaming video content
     */
    function start()
    {
        $this->open();
        $this->setHeader();
        $this->stream();
        $this->end();
    }
}
