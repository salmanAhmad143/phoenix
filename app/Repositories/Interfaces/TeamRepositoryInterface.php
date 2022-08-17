<?php

namespace App\Repositories\Interfaces;

interface TeamRepositoryInterface
{
    public function listTeam($param);

    public function getTeam($where);

    public function addTeam($param);

    public function updateTeam($param);

    public function deleteTeam($where);

    public function listTeamMember($param);

    public function getTeamMember($where);

    public function addTeamMember($param);

    public function removeTeamMember($where);
}
