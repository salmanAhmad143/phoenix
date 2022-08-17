<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\ProjectTeam;
use App\Models\ProjectUser;
use App\Repositories\Interfaces\ProjectRepositoryInterface;
use App\Helpers\common;


class ProjectRepository implements ProjectRepositoryInterface
{
    public function paginateProject($param)
    {
        if (isset($param['userId'])) {
            $project = $this->userProject($param);
            if ( !empty(auth()->user()->accessLevel->codeValue) && auth()->user()->accessLevel->codeValue == "Hierarchy" ) {
                $employeeDept = auth()->user()->department->codeValue;
                if (in_array($employeeDept, common::salesRelatedDept)) {
                    $ids = common::getUserAccessIds();
                    $pocClientIds = common::getPocsClientIds($ids['arrayValue']);
                    $opocClientIds = common::getOpocsClientIds($ids['arrayValue']);
                    $currentClientIds = common::getClientIds($ids['arrayValue']);
                    //Adding condition for the project access check.
                    $project->orwhereIn('project.projectLeadId', $ids['arrayValue']);
                    $project->orwhereIn('project.projectManagerId', $ids['arrayValue']);
                    $project->orwhereIn('project.createdBy', $ids['arrayValue']);
                    //Adding condition for the sales poc access check.
                    if (!empty($pocClientIds['poc_clientIds'])) {
                        $project->orWhereIn('project.clientId', explode(",", $pocClientIds['poc_clientIds']));
                    }
                    //Adding condition for the Operational poc access check.
                    if (!empty($opocClientIds['opoc_clientIds'])) {
                        $project->orWhereIn('project.clientId', explode(",", $opocClientIds['opoc_clientIds']));
                    }
                    if (!empty($currentClientIds['clientIds'])) {
                        $project->orWhereIn('project.clientId', explode(",", $currentClientIds['clientIds']));
                    }
                } else {
                    $pocClientIds = common::getPocsClientIds([$param['userId']]);
                    $opocClientIds = common::getOpocsClientIds([$param['userId']]);
                    $currentClientIds = common::getClientIds([$param['userId']]);
                    //Adding condition for the sales poc access check.
                    if (!empty($pocClientIds['poc_clientIds'])) {
                        $project->orWhereIn('project.clientId', explode(",", $pocClientIds['poc_clientIds']));
                    }
                    //Adding condition for the Operational poc access check.
                    if (!empty($opocClientIds['opoc_clientIds'])) {
                        $project->orWhereIn('project.clientId', explode(",", $opocClientIds['opoc_clientIds']));
                    }
                    if (!empty($currentClientIds['clientIds'])) {
                        $project->orWhereIn('project.clientId', explode(",", $currentClientIds['clientIds']));
                    }
                    $project->orwhere('project.projectLeadId', $param['userId']);
                    $project->orwhere('project.projectManagerId', $param['userId']);
                    $project->orwhere('project.createdBy', $param['userId']);
                }
            }
        } else {
            $project = Project::query();
            if (isset($param['where']) && count($param['where']) > 0) {
                $project->where($param['where'])->orderBy('ProjectId', 'DESC');
            }
        }
        return $project->paginate($param['size']);
    }

    public function listProject($param)
    {
        if (isset($param['userId'])) {
            $project = $this->userProject($param);
        } else {
            $project = Project::query();
            if (isset($param['where']) && count($param['where']) > 0) {
                $project->where($param['where'])->orderBy('ProjectId', 'DESC');
            }
        }
        return $project->orderBy('projectId', 'DESC')->get();
    }

    public function getProject($param)
    {
        if (isset($param['userId'])) {
            $project = $this->userProject($param);
            if (!empty($param['where']['projectId'][0])) {
                $project->orwhere('projectId', '=', $param['where']['projectId'][0]);
            }
        } else {
            $project = Project::where($param['where']);
        }
        return $project->first();
    }

    public function userProject($param)
    {
        $userId = $param['userId'];
        $project = Project::with('transcription:workflowId,name,order,workflowType');
        $project->with('translation:workflowId,name,order,workflowType');
        $project->with('projectLead:userId,name,email');
        $project->with('projectManager:userId,name,email');
        $project->with('projectClient:clientId,clientName');
        $project->with(['projectTags' => function($tag) {
            $tag->with('TagData:tagId,tag');
        }]);
        return $project->where(function ($project) use ($userId) {
            $project->whereHas('projectUser', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('projectTeam.teamMember', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('media.mediaUser', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('media.mediaTeam.teamMember', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            })->orWhereHas('media.mediaTranscript', function ($query) use ($userId) {
                $query->where('linguistId', '=', $userId);
            });
        })->where($param['where'])->orderBy('projectId', 'DESC');
    }

    public function addProject($param)
    {
        $project = Project::create($param['indicator']);
        $this->addProjectUser([
            'projectId' => $project->projectId,
            'userId' => [auth()->user()->userId],
        ]);

        return $project;
    }

    public function updateProject($param)
    {
        $project = Project::where($param['where'])->first();
        foreach ($param['indicator'] as $key => $value) {
            $project->$key = $value;
        }
        return $project->save();
    }

    public function deleteProject($where)
    {
        $project = Project::where($where);
        if ($project !== null) {
            $project->delete();
        }
    }

    public function listProjectUser($param)
    {
        return ProjectUser::where($param['where'])->get();
    }

    public function getProjectUser($where)
    {
        return ProjectUser::where($where)->first();
    }

    public function addProjectUser($param)
    {
        $indicator = [
            'createdBy' => auth()->user()->userId,
            'createdAt' => Carbon::now()->toDateTimeString(),
        ];

        $projectUser = ProjectUser::where(function($query) use($param){
            $query->where('projectId', $param['projectId']);
            $query->whereIn('userId', $param['userId']);
        })->get();

        if ($projectUser->isEmpty()) {  
            foreach ($param['userId'] as $userId) {
                $where = [
                    'projectId' => $param['projectId'],
                    'userId' => $userId,
                ];
                ProjectUser::firstOrCreate($where, $indicator);
            }
            return true;
        } else {
            return false;
        }
    }

    public function removeProjectUser($where)
    {
        $projectUser = ProjectUser::where($where);
        if ($projectUser !== null) {
            $projectUser->delete();
        }
    }

    public function listProjectTeam($param)
    {
        return ProjectTeam::where($param['where'])->get();
    }

    public function getProjectTeam($where)
    {
        return ProjectTeam::where($where)->first();
    }

    public function getProjectTeamUser($param)
    {
        $userId = $param['userId'];
        return Project::where(function ($project) use ($userId) {
            $project->WhereHas('projectTeam.teamMember', function ($query) use ($userId) {
                $query->where('userId', '=', $userId);
            });
        })->where($param['where'])->first();
    }

    public function addProjectTeam($param)
    {
        $indicator = [
            'createdBy' => auth()->user()->userId,
            'createdAt' => Carbon::now()->toDateTimeString(),
        ];
        foreach ($param['teamId'] as $teamId) {
            $where = [
                'projectId' => $param['projectId'],
                'teamId' => $teamId,
            ];
            ProjectTeam::firstOrCreate($where, $indicator);
        }
    }

    public function removeProjectTeam($where)
    {
        $projectTeam = ProjectTeam::where($where);
        if ($projectTeam !== null) {
            $projectTeam->delete();
        }
    }
}
