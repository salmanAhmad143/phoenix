<?php

namespace App\Repositories\Interfaces;

interface WorkflowTransitionRepositoryInterface
{
    public function listWorkflowTransition($param);

    public function getWorkflowTransition($where);

    public function addWorkflowTransition($param);

    public function updateWorkflowTransition($param);

    public function deleteWorkflowTransition($where);
}
