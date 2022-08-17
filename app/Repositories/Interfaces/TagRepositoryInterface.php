<?php

namespace App\Repositories\Interfaces;

interface TagRepositoryInterface
{
    public function listTag($param);

    public function getTag($where);

    public function addTag($param);

    public function updateTag($param);

    public function deleteTag($where);
}
