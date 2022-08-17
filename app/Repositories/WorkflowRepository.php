<?php

namespace App\Repositories;

use App\Models\Workflow;
use App\Repositories\Interfaces\WorkflowRepositoryInterface;

class WorkflowRepository implements WorkflowRepositoryInterface
{
    public function listWorkflow($param)
    {
        $workflow = Workflow::query();
        $workflow->with('workflow:id,codeValue as workFlowType');
        if (!empty($param['whereHas'])) {
            $workFlowType= $param['whereHas'];
            $workflow->whereHas('workflow', function($q) use ($workFlowType) {
                $q->where('codeValue', '=', $workFlowType);
            });
        }
        if (isset($param['select']) && count($param['select']) > 0) {
            $workflow->select($param['select']);
        }
        if (isset($param['where']) && count($param['where']) > 0) {
            $workflow->where($param['where']);
        }
        if (isset($param['orderBy']) && count($param['orderBy']) > 0) {
            foreach ($param['orderBy'] as $column => $direction) {
                $workflow->orderBy($column, $direction);
            }
        }
        return $workflow->get();
    }

    public function getWorkflow($where)
    {
    }

    public function addWorkflow($param)
    {
        return Workflow::create($param['indicator']);
    }

    public function updateWorkflow($param)
    {
        $mediaTranscript = Workflow::where($param['where'])->orderBy('mediaTranscriptId', 'desc')->first();
        foreach ($param['indicator'] as $key => $value) {
            $mediaTranscript->$key = $value;
        }
        $mediaTranscript->save();
    }

    public function deleteWorkflow($where)
    {
        $mediaTranscript = Workflow::where($where)->orderBy('mediaTranscriptId', 'desc')->first();
        if ($mediaTranscript !== null) {
            $mediaTranscript->delete();
        }

        return $mediaTranscript;
    }
}
