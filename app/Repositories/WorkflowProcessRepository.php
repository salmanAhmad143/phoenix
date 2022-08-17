<?php

namespace App\Repositories;

use App\Models\WorkflowProcess;
use App\Repositories\Interfaces\WorkflowProcessRepositoryInterface;

class WorkflowProcessRepository implements WorkflowProcessRepositoryInterface
{
    public function listWorkflowProcess($param)
    {
        $workflowProcess = WorkflowProcess::query();
        $workflowProcess->with('workflowTransition:transitionId,name');
        if (isset($param['select']) && count($param['select']) > 0) {
            $workflowProcess->select($param['select']);
        }
        if (isset($param['where']) && count($param['where']) > 0) {
            $workflowProcess->where($param['where']);
        }
        if (isset($param['orderBy']) && count($param['orderBy']) > 0) {
            foreach ($param['orderBy'] as $column => $direction) {
                $workflowProcess->orderBy($column, $direction);
            }
        }
        return $workflowProcess->get();
    }

    public function getWorkflowProcess($param)
    {
        $workflowProcess = WorkflowProcess::query();
        if (isset($param['select']) && count($param['select']) > 0) {
            $workflowProcess->select($param['select']);
        }
        if (isset($param['where']) && count($param['where']) > 0) {
            $workflowProcess->where($param['where']);
        }
        return $workflowProcess->first();
    }

    public function addWorkflowProcess($param)
    {
        return WorkflowProcess::create($param['indicator']);
    }

    public function updateWorkflowProcess($param)
    {
        $mediaTranscript = WorkflowProcess::where($param['where'])->orderBy('mediaTranscriptId', 'desc')->first();
        foreach ($param['indicator'] as $key => $value) {
            $mediaTranscript->$key = $value;
        }
        $mediaTranscript->save();
    }

    public function deleteWorkflowProcess($where)
    {
        $mediaTranscript = WorkflowProcess::where($where)->orderBy('mediaTranscriptId', 'desc')->first();
        if ($mediaTranscript !== null) {
            $mediaTranscript->delete();
        }

        return $mediaTranscript;
    }
}
