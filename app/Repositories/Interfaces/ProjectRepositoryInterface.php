<?php

namespace App\Repositories\Interfaces;

interface ProjectRepositoryInterface
{
    public function paginateProject($param);

    public function listProject($param);

    public function userProject($param);

    public function getProject($param);

    public function addProject($param);

    public function updateProject($param);

    public function deleteProject($where);

    public function listProjectUser($param);

    public function getProjectUser($where);

    public function addProjectUser($param);

    public function removeProjectUser($where);

    public function listProjectTeam($param);

    public function getProjectTeam($where);

    public function getProjectTeamUser($param);

    public function addProjectTeam($param);

    public function removeProjectTeam($where);
}
