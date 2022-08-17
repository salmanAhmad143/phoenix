<?php

namespace App\Repositories;

use App\Models\MediaTranscript;
use App\Repositories\Interfaces\TranscriptAssignmentRepositoryInterface;

class TranscriptAssignmentRepository implements TranscriptAssignmentRepositoryInterface
{
    public function assignTranscript($param)
    {
        $transcript = MediaTranscript::where($param['where']);
        $transcript->where('languageId', '=', $param['languageId']);
        if (isset($param['orderBy']) && count($param['orderBy']) > 0) {
            foreach ($param['orderBy'] as $column => $direction) {
                $transcript->orderBy($column, $direction);
            }
        }
        return $transcript->first();
    }
}
