<?php

namespace App\Repositories\Interfaces;

interface ContentRepositoryInterface
{

    public function paginateContent($param);

    public function listContent($param);

    public function getContent($where);

    public function addContent($param);

    public function updateContent($param);

    public function deleteContent($where);
}
