<?php

namespace App\Repositories;

use Carbon\Carbon;

use App\Models\Team;
use App\Models\TeamMember;
use App\Repositories\Interfaces\TeamRepositoryInterface;

class TeamRepository implements TeamRepositoryInterface
{
    public function listTeam($param)
    {
        $team = Team::query();
        if (isset($param['where']) && count($param['where']) > 0) {
            $team->where($param['where']);
        }

        if($param['size'] === 'all') {
            return $team->get();
        } else {
            return $team->paginate($param['size']);
        }
    }

    public function getTeam($where)
    {
        return Team::where($where)->first();
    }

    public function addTeam($param)
    {
        $team = new Team();
        $team->name = $param['name'];
        $team->createdBy = auth()->user()->userId;
        $team->createdAt = Carbon::now()->toDateTimeString();
        $team->save();
    }

    public function updateTeam($param)
    {
        $team = Team::where($param['where'])->first();
        $team->name = $param['name'];
        $team->updatedBy = auth()->user()->userId;
        $team->updatedAt = Carbon::now()->toDateTimeString();
        $team->save();
    }

    public function deleteTeam($where)
    {
        $team = Team::where($where);
        if ($team !== null) {
            $team->delete();
        }
    }

    public function listTeamMember($param)
    {
        return TeamMember::where($param['where'])->get();
    }

    public function getTeamMember($where)
    {
        return TeamMember::where($where)->first();
    }

    public function addTeamMember($param)
    {
        $indicator = [
            'createdBy' => auth()->user()->userId,
            'createdAt' => Carbon::now()->toDateTimeString(),
        ];

        $teamMember = TeamMember::where(function($query) use($param){
            $query->where('teamId', $param['teamId']);
            $query->whereIn('userId', $param['userId']);
        })->get();
        
        if ($teamMember->isEmpty()) {            
            foreach ($param['userId'] as $userId) {
                $where = [
                    'teamId' => $param['teamId'],
                    'userId' => $userId,
                ];
                TeamMember::firstOrCreate($where, $indicator);
            }
            return true;
        } else {
            return false;
        }
    }

    public function removeTeamMember($where)
    {
        $teamMember = TeamMember::where($where);
        if ($teamMember !== null) {
            $teamMember->delete();
        }
    }
}
