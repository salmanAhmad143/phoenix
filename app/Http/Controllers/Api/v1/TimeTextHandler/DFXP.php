<?php

namespace App\Http\Controllers\Api\v1\TimeTextHandler;

use App\Http\Controllers\Api\v1\Interfaces\TimeText;
use Done\Subtitles\Subtitles;

class DFXP implements TimeText
{
    public function __construct()
    {
        //
    }

    public function import($param)
    {
        /**
         * Source: https://github.com/mantas-done/subtitles
         * Install: composer require mantas-done/subtitles
         */
        $subtitles = Subtitles::load($param['fileContent'], 'dfxp');
        $subtitles = $subtitles->getInternalFormat();
        foreach ($subtitles as $subtitle) {
            $caption['startTime'] = $subtitle['start'] * 1000;
            $caption['endTime'] = $subtitle['end'] * 1000;
            $caption['text'] = '';
            foreach ($subtitle['lines'] as $line) {
                $caption['text'] .= $line . PHP_EOL;
            }
            $caption['text'] = rtrim($caption['text'], PHP_EOL);
            $captions[] = $caption;
        }
        return $captions;
    }

    public function export($param)
    {
        $subtitles = new Subtitles();
        $success = false;
        $data = "";
        $textColumn = $param['textColumn'];
        foreach ($param['captions'] as $caption) {
            if ($caption->$textColumn) {
                $success = true;
            }
            $subtitles->add($caption->startTime / 1000, $caption->endTime / 1000, $caption->$textColumn);
        }
        if ($success == true) {
            $data = $subtitles->content('dfxp');
        }
        // $subtitles->save('subtitles.dfxp');
        return [
            'success' => $success,
            'data' => $data
        ];
    }
}
