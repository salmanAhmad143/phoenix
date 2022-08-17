<?php

namespace App\Repositories;

use App\Models\MediaTranscript;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\TranslateCaptionRepositoryInterface;

class TranslateCaptionRepository implements TranslateCaptionRepositoryInterface
{
    public function listTranslate($param)
    {
        if (isset($param['userId'])) {
            $translate = $this->userProjectMediaTranslate($param);
        } else {
            $translate = MediaTranscript::query();
        }
        $translate->with('language');
        $translate->with('linguist:userId,name,email');
        $translate->with('workflow:workflowId,name');
        $translate->with('workflowState:stateId,name');
        $translate->with('media:mediaId');
        if (isset($param['where']) && count($param['where']) > 0) {
            if ($param['whereGroupByLast']) {
                // $translate->whereIn('mediaTranscriptId', [DB::table('media_transcript')->select('mediaTranscriptId')->where($param['where'])->groupBy('languageId')->max('mediaTranscriptId')]);
                $translate->whereRaw('mediaTranscriptId in (select max(mediaTranscriptId) from media_transcript where ' . $param['whereGroupByLast'] . ' group by (languageId))');
            } else {
                $translate->where($param['where']);
            }
        }
        if (isset($param['orderBy']) && count($param['orderBy']) > 0) {
            foreach ($param['orderBy'] as $column => $direction) {
                $translate->orderBy($column, $direction);
            }
        }
        return $translate->get();
    }

    public function getTranslate($param)
    {
        $translate = MediaTranscript::where($param['where']);
        $translate->where('languageId', '!=', $param['languageId']);
        if (isset($param['orderBy']) && count($param['orderBy']) > 0) {
            foreach ($param['orderBy'] as $column => $direction) {
                $translate->orderBy($column, $direction);
            }
        }
        return $translate->first();
    }

    public function userProjectMediaTranslate($param)
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

    public function addTranslate($param)
    {
        return MediaTranscript::create($param['indicator']);
    }

    public function updateTranslate($param)
    {
        $mediaTranscript = MediaTranscript::with('language')->with('linguist:userId,name,email')->with('workflow:workflowId,name')->with('workflowState:stateId,name')->where($param['where'])->orderBy('mediaTranscriptId', 'desc')->first();
        // foreach ($param['indicator'] as $key => $value) {
        //     $mediaTranscript->$key = $value;
        // }
        $mediaTranscript->fill($param['indicator']);
        $mediaTranscript->save();
        return $mediaTranscript;
    }

    public function deleteTranslate($where)
    {
        $mediaTranscripts = MediaTranscript::where($where)->get();
        if ($mediaTranscripts !== null) {
            MediaTranscript::where($where)->delete();
        }

        return $mediaTranscripts;
    }
}
