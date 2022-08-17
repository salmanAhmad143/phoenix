<?php

namespace App\Repositories\Interfaces;

interface WorkflowProcessRepositoryInterface
{
    public function listWorkflowProcess($param);

    public function getWorkflowProcess($where);

    public function addWorkflowProcess($param);

    public function updateWorkflowProcess($param);

    public function deleteWorkflowProcess($where);
}
