<?php

namespace App\Repositories\Interfaces;

interface ProjectTagRepositoryInterface
{
    public function listProjectTag($param);

    public function getProjectTag($where);

    public function addProjectTag($param);

    public function updateProjectTag($param);

    public function deleteProjectTag($where);
}
