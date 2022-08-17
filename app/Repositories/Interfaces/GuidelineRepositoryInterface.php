<?php

namespace App\Repositories\Interfaces;

interface GuidelineRepositoryInterface
{
    public function listGuideline($param);

    public function getGuideline($where);

    public function addGuideline($param);

    public function updateGuideline($param);

    public function updateGuidelines($param);

    public function deleteGuideline($where);
}
