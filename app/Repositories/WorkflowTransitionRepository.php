<?php

namespace App\Repositories;

use App\Models\WorkflowTransition;
use App\Repositories\Interfaces\WorkflowTransitionRepositoryInterface;

class WorkflowTransitionRepository implements WorkflowTransitionRepositoryInterface
{
    public function listWorkflowTransition($param)
    {
        //write logic
    }

    public function getWorkflowTransition($param)
    {
        $workflowTransition = WorkflowTransition::query();
        if (isset($param['select']) && count($param['select']) > 0) {
            $workflowTransition->select($param['select']);
        }
        if (isset($param['where']) && count($param['where']) > 0) {
            $workflowTransition->where($param['where']);
        }
        return $workflowTransition->first();
    }

    public function addWorkflowTransition($param)
    {
        //write logic
    }

    public function updateWorkflowTransition($param)
    {
        //write logic
    }

    public function deleteWorkflowTransition($where)
    {
        //write logic
    }
}
