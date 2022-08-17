<?php

namespace App\Repositories\Interfaces;

interface MediaRepositoryInterface
{
    public function listMedia($param);

    public function listUserMedia($param);

    public function getMedia($param);

    public function addMedia($param);

    public function updateMedia($param);

    public function deleteMedia($where);

    public function listMediaUser($param);

    public function getMediaUser($where);

    public function addMediaUser($param);

    public function removeMediaUser($where);

    public function listMediaTeam($param);

    public function getMediaTeam($where);

    public function addMediaTeam($param);

    public function removeMediaTeam($where);
}
