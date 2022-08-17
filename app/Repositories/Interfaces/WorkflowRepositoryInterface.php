<?php

namespace App\Repositories\Interfaces;

interface WorkflowRepositoryInterface
{
    public function listWorkflow($param);

    public function getWorkflow($where);

    public function addWorkflow($param);

    public function updateWorkflow($param);

    public function deleteWorkflow($where);
}
