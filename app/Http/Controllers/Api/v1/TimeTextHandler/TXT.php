<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\Api\v1\TimeTextHandler;

use App\Http\Controllers\Api\v1\Interfaces\TimeText;

/**
 * Description of TXT
 *
 * @author LC-1016
 */
class TXT implements TimeText
{
    public function import($param)
    {
        if ($param['guideline']['textBreakBy'] == "sentence") {
            $texts = explode($param['sentenceBreaker'] ?? ".", $param['fileContent']);
        } else if ($param['guideline']['textBreakBy'] == "paragraph") {
            $texts = explode(PHP_EOL, $param['fileContent']);
        }
        $timeDiff = round($param['mediaDuration'] / COUNT($texts));
        $captions = [];
        $caption = [];
        foreach ($texts as $text) {
            $caption['startTime'] = ($caption['endTime'] ?? 0) + 1;
            $caption['endTime'] = $caption['startTime'] + $timeDiff;
            $caption['text'] = ltrim(str_replace(PHP_EOL, '', $text), ' ');
            $captions[] = $caption;
        }
        return $captions;
    }

    public function export($param)
    {
        $success = false;
        $data = "";
        $textColumn = $param['textColumn'];
        foreach ($param['captions'] as $caption) {
            if ($caption->$textColumn) {
                $success = true;
                $data .= $caption->$textColumn . PHP_EOL;
            }
        }
        return [
            'success' => $success,
            'data' => $data
        ];
    }
}
