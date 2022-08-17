<?php

namespace App\Libraries;

use FFMpeg;
use Exception;
use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;

class FFMpegHandler extends Controller
{

    /**
     * Reference: https://github.com/PHP-FFMpeg/PHP-FFMpeg
     * Resources ffprobe -v error -select_streams v:0 -show_entries stream=avg_frame_rate -of default=noprint_wrappers=1:nokey=1 filename.ext
     * Resources https://trac.ffmpeg.org/wiki/FFprobeTips
     * Request: mediaPath
     * Response: Response will be data array includes 'duration', 'frameRate', 'sampleRate', 'bitRate', channels, and streams
     */
    public function getMediaInfo($mediaPath)
    {
        $mediaInfo = array();

        try {
            $streams = shell_exec(env('FFMPROBE_BINARY_PATH') . ' -v error -show_streams -print_format json "' . $mediaPath . '"');

            $mediaInfo['streamDetails'] = $streams;

            $streams = json_decode($streams, true);

            if (!empty($streams)) {
                //Getting array keys for audio and video base on codec_type
                $videoKey = array_search('video', array_column($streams['streams'], 'codec_type'));
                $audioKey = array_search('audio', array_column($streams['streams'], 'codec_type'));

                //TODO: FLAM-192: improve this to check if audio exists or not before uploading files.
                if ($audioKey === false) {
                    throw new CustomException("There are no audio track in media file");
                }

                //Getting video details
                if ($videoKey !== false) {
                    list($frn, $frd) = explode("/", $streams['streams'][$videoKey]['r_frame_rate']);
                    $frameRate = round($frn / $frd, 5);
                    $mediaInfo['frameRate'] = $frameRate > 99 ? null : $frameRate; //TODO: fix this hard coded 99 to not get frame rate from cover art of audios files
                    $mediaInfo['bitRate'] = $streams['streams'][$videoKey]['bit_rate'] ?? null;
                }

                //Getting audio details
                if ($audioKey !== false) {
                    $mediaInfo['audioChannels'] = $streams['streams'][$audioKey]['channels'] ?? null;
                    $mediaInfo['sampleRate'] = $streams['streams'][$audioKey]['sample_rate'] ?? null;
                    $mediaInfo['duration'] = $streams['streams'][$audioKey]['duration'] ?? null;
                    if ($mediaInfo['duration'] === null) {
                        //TODO: This method doesn't support MKV as audio duration are save in tags field, fix this for future
                        throw new CustomException("Video not supported!");
                    } else {
                        $mediaInfo['duration'] = $mediaInfo['duration'] * 1000;
                    }
                }
            } else {
                throw new CustomException("Unable to fetch media specifications. Try using MP4.");
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

        return [
            "success" => true,
            "data" => $mediaInfo ?? [],
        ];
    }

    /*
     * Request: $file-'Source video path', 'Target audio path'
     * Response: audioPath
     */
    public function convertToAudioFlac($videoPath)
    {
        try {
            $ffmpegConfig = array (
                'ffmpeg.binaries' => env('FFMPEG_BINARY_PATH'),
                'ffprobe.binaries' => env('FFMPROBE_BINARY_PATH'),
                'timeout' => 3600, // The timeout for the underlying process
                'ffmpeg.threads' => 12, // The number of threads that FFMpeg should use
            );

            $ffmpegHandler = FFMpeg\FFMpeg::create($ffmpegConfig, null);

            $ffmpegVideo = $ffmpegHandler->open($videoPath);
            //$audioFormat = new FFMpeg\Format\Audio\Mp3();
            $audioFormat = new FFMpeg\Format\Audio\Flac();
            //new FFMpeg\Format\Audio\Wav()

            // $audioFormat->on('progress', function ($audio, $format, $percentage){
            // echo "$percentage % transcoded";
            // });

            /*$audioFormat
                ->setAudioChannels(2)
                ->setAudioKiloBitrate(64);*/

            $mediaParts = pathinfo($videoPath);
            $mediaName = $mediaParts['filename'];
            $audioPath = dirname($videoPath) . "/$mediaName.flac";
            $ffmpegVideo->save($audioFormat, $audioPath);
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
            "message" => "Video converted to audio successfully",
            "data" => ['audioPath' => $audioPath],
        ];
    }

    public function convertToAudioMP3($videoPath)
    {
        try {
            $ffmpegConfig = array (
                'ffmpeg.binaries' => env('FFMPEG_BINARY_PATH'),
                'ffprobe.binaries' => env('FFMPROBE_BINARY_PATH'),
                'timeout' => 3600, // The timeout for the underlying process
                'ffmpeg.threads' => 12, // The number of threads that FFMpeg should use
            );

            $ffmpegHandler = FFMpeg\FFMpeg::create($ffmpegConfig, null);

            $ffmpegVideo = $ffmpegHandler->open($videoPath);
            $audioFormat = new FFMpeg\Format\Audio\Mp3();

            $mediaParts = pathinfo($videoPath);
            $mediaName = $mediaParts['filename'];
            $audioPath = dirname($videoPath) . "/$mediaName.mp3";
            $ffmpegVideo->save($audioFormat, $audioPath);
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
            "message" => "Video converted to audio successfully",
            "data" => ['audioPath' => $audioPath],
        ];
    }
    
    public function getThumbnail($videoPath)
    {
        try {
            $mediaParts = pathinfo($videoPath);
            $mediaName = $mediaParts['filename'];
            $videoImagePath = dirname($videoPath) . "/$mediaName.jpg";

            $ffmpeg = FFMpeg\FFMpeg::create(array(
                'ffmpeg.binaries' => env('FFMPEG_BINARY_PATH'),
                'ffprobe.binaries' => env('FFMPROBE_BINARY_PATH'),
                'timeout' => 3600, // The timeout for the underlying process
                'ffmpeg.threads' => 12, // The number of threads that FFMpeg should use
            ), null);
            $video = $ffmpeg->open($videoPath);
            $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(env('VIDEO_IMAGE_FRAME_SECOND')))->save($videoImagePath);
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
            "message" => "Video converted to audio successfully",
            "data" => [
                'videoImagePath' => $videoImagePath,
                'videoImageName' => "$mediaName.jpg"
            ],
        ];
    }
}
