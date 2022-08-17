<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\MediaCaption;
use App\Models\MediaTranscript;
use App\Repositories\Interfaces\CaptionRepositoryInterface;

class CaptionRepository implements CaptionRepositoryInterface
{
    public function insertSourceText($param)
    {
        $captions = $this->listCaption($param);
        MediaCaption::where('mediaTranscriptId', $param['mediaTranscriptId'])->delete();
        foreach ($captions as $caption) {
            $values[] = [
                'mediaTranscriptId' => $param['mediaTranscriptId'],
                'startTime' => $caption->startTime,
                'endTime' => $caption->endTime,
                'sourceText' => $caption->sourceText,
                'targetText' => $caption->targetText,
                'createdBy' => $param['userId'] ?? auth()->user()->userId,
                'createdAt' => Carbon::now()->toDateTimeString(),
            ];
        }
        return MediaCaption::insert($values ?? []);
    }

    public function listCaption($param)
    {
        $mediaTranscript = MediaTranscript::with(['mediaCaption' => function ($query) {
            $query->orderBy('startTime', 'asc');
        }])->where($param['where'])->orderBy('mediaTranscriptId', 'desc')->first();
        return $mediaTranscript->mediaCaption ?? null;
    }

    public function getCaption($where)
    {
        return MediaCaption::where($where)->first();
    }

    public function getTranscriptionId($where)
    {
        return MediaTranscript::where($where)->orderBy('mediaTranscriptId', 'desc')->first();
    }

    public function addCaption($param)
    {
        return MediaCaption::create($param['indicator']);
    }

    public function insertCaption($param)
    {
        return MediaCaption::insert($param['indicator']);
    }

    public function updateCaption($param)
    {
        $mediaCaption = MediaCaption::where($param['where'])->first();
        foreach ($param['indicator'] as $key => $value) {
            $mediaCaption->$key = $value;
        }
        $mediaCaption->save();
    }

    public function deleteCaption($where)
    {
        MediaCaption::where($where)->delete();
        // $mediaCaption = MediaCaption::where($where)->first();
        // if ($mediaCaption !== null) {
        //     $mediaCaption->delete();
        // }
    }
}
