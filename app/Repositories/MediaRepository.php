<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Media;
use App\Models\MediaTeam;
use App\Models\MediaUser;
use App\Repositories\Interfaces\MediaRepositoryInterface;

class MediaRepository implements MediaRepositoryInterface
{
    public function listMedia($param)
    {
        if (isset($param['userId'])) {
            $media = $this->userProjectMedia($param);
        } else {
            $media = Media::query();
            if (isset($param['where']) && count($param['where']) > 0) {
                $media->where($param['where']);
            }
        }
        return $media->get();
    }

    public function listUserMedia($param)
    {
        $userId = $param['userId'];
        return Media::where(function ($media) use ($userId) {
            $media->whereHas('mediaUser', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('mediaTeam.teamMember', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('mediaTranscript', function ($query) use ($userId) {
                $query->where('linguistId', '=', $userId);
            });
        })->where($param['where'])->paginate($param['size']);
    }

    public function getMedia($param)
    {
        if (isset($param['userId'])) {
            $media = $this->userProjectMedia($param);
        } else {
            $media = Media::with('project')->where($param['where']);
        }
        return $media->first();
    }

    public function userProjectMedia($param)
    {
        $userId = $param['userId'];
        return Media::with('project')->where(function ($project) use ($userId) {
            $project->whereHas('project.projectUser', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('project.projectTeam.teamMember', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('mediaUser', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('mediaTeam.teamMember', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('mediaTranscript', function ($query) use ($userId) {
                $query->where('linguistId', '=', $userId);
            });
        })->where($param['where']);
    }

    public function addMedia($param)
    {
        return Media::create($param['indicator']);
    }

    public function updateMedia($param)
    {
        $media = Media::where($param['where'])->first();
        foreach ($param['indicator'] as $key => $value) {
            $media->$key = $value;
        }
        $media->save();
    }

    public function deleteMedia($where)
    {
        $media = Media::where($where);
        if ($media !== null) {
            $media->delete();
        }
    }

    public function listMediaUser($param)
    {
        return MediaUser::where($param['where'])->get();
    }

    public function getMediaUser($where)
    {
        return MediaUser::where($where)->first();
    }

    public function addMediaUser($param)
    {
        $indicator = [
            'createdBy' => auth()->user()->userId,
            'createdAt' => Carbon::now()->toDateTimeString(),
        ];
        $mediaUser = MediaUser::where(function($query) use($param){
            $query->where('mediaId', $param['mediaId']);
            $query->whereIn('userId', $param['userId']);
        })->get();

        if ($mediaUser->isEmpty()) { 
            foreach ($param['userId'] as $userId) {
                $where = [
                    'mediaId' => $param['mediaId'],
                    'userId' => $userId,
                ];
                MediaUser::firstOrCreate($where, $indicator);
            }
            return true;
        } else {
            return false;
        }
    }

    public function removeMediaUser($where)
    {
        $mediaUser = MediaUser::where($where);
        if ($mediaUser !== null) {
            $mediaUser->delete();
        }
    }

    public function listMediaTeam($param)
    {
        return MediaTeam::where($param['where'])->get();
    }

    public function getMediaTeam($where)
    {
        return MediaTeam::where($where)->first();
    }

    public function addMediaTeam($param)
    {
        $indicator = [
            'createdBy' => auth()->user()->userId,
            'createdAt' => Carbon::now()->toDateTimeString(),
        ];
        foreach ($param['teamId'] as $teamId) {
            $where = [
                'mediaId' => $param['mediaId'],
                'teamId' => $teamId,
            ];
            MediaTeam::firstOrCreate($where, $indicator);
        }
    }

    public function removeMediaTeam($where)
    {
        $mediaTeam = MediaTeam::where($where);
        if ($mediaTeam !== null) {
            $mediaTeam->delete();
        }
    }
}
