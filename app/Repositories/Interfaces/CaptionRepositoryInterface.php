<?php

namespace App\Repositories\Interfaces;

interface CaptionRepositoryInterface
{
    public function insertSourceText($param);

    public function listCaption($param);

    public function getCaption($where);

    public function getTranscriptionId($where);

    public function addCaption($param);

    public function insertCaption($param);

    public function updateCaption($param);

    public function deleteCaption($where);
}
