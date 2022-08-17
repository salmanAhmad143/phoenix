<?php

namespace App\Repositories;

use App\Models\ProjectTag;
use App\Repositories\Interfaces\ProjectTagRepositoryInterface;

class ProjectTagRepository implements ProjectTagRepositoryInterface
{
    public function listProjectTag($param)
    {
        //code
    }

    public function getProjectTag($where)
    {
        //code
    }

    public function addProjectTag($param)
    {    
        return ProjectTag::create($param['indicator']);
    }

    public function updateProjectTag($param)
    {
        //code
    }

    public function deleteProjectTag($where)
    {
        $projectData = ProjectTag::where($where);
        if ($projectData !== null) {
            $projectData->delete();
        }
    }
}
