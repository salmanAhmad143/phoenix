<?php

namespace App\Repositories;

use App\Models\MediaTranscript;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\TranscriptCaptionRepositoryInterface;

class TranscriptCaptionRepository implements TranscriptCaptionRepositoryInterface
{
    public function listTranscript($param)
    {
        if (isset($param['userId'])) {
            $transcript = $this->userProjectMediaTranscript($param);
        } else {
            $transcript = MediaTranscript::query();
        }
        $transcript->with('language');
        $transcript->with('linguist:userId,name,email');
        $transcript->with('workflow:workflowId,name');
        $transcript->with('workflowState:stateId,name');
        $transcript->with('media:mediaId');
        if (isset($param['where']) && count($param['where']) > 0) {
            if (!empty($param['whereGroupByLast'])) {
                $transcript->whereRaw('mediaTranscriptId in (select max(mediaTranscriptId) from media_transcript where ' . $param['whereGroupByLast'] . ' group by (languageId))');
            } else {
                $transcript->where($param['where']);
            }
        }
        if (isset($param['orderBy']) && count($param['orderBy']) > 0) {
            foreach ($param['orderBy'] as $column => $direction) {
                $transcript->orderBy($column, $direction);
            }
        }
        return $transcript->get();
    }

    public function getTranscript($param)
    {
        $transcript = MediaTranscript::where($param['where']);

        if (!empty($param['languageId'])) {
            $transcript->where('languageId', '=', $param['languageId']);
        }

        if (isset($param['orderBy']) && count($param['orderBy']) > 0) {
            foreach ($param['orderBy'] as $column => $direction) {
                $transcript->orderBy($column, $direction);
            }
        }
        return $transcript->first();
    }

    public function userProjectMediaTranscript($param)
    {
        $userId = $param['userId'];
        return MediaTranscript::where(function ($project) use ($userId) {
            $project->whereHas('media.project.projectUser', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('media.project.projectTeam.teamMember', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('media.mediaUser', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('media.mediaTeam.teamMember', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhere('linguistId', '=', $userId);
        });
    }

    public function addTranscript($param)
    {
        return MediaTranscript::create($param['indicator']);
    }

    public function updateTranscript($param)
    {
        $mediaTranscript = MediaTranscript::with('language')->with('linguist:userId,name,email')->with('workflow:workflowId,name')->with('workflowState:stateId,name')->where($param['where'])->orderBy('mediaTranscriptId', 'desc')->first();
        // foreach ($param['indicator'] as $key => $value) {
        //     $mediaTranscript->$key = $value;
        // }
        $mediaTranscript->fill($param['indicator']);
        $mediaTranscript->save();
        return $mediaTranscript;
    }

    public function deleteTranscript($where)
    {
        $mediaTranscripts = MediaTranscript::where($where)->get();
        if ($mediaTranscripts !== null) {
            MediaTranscript::where($where)->delete();
        }

        return $mediaTranscripts;
    }
}
