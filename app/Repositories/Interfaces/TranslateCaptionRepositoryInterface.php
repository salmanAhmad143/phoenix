<?php

namespace App\Repositories\Interfaces;

interface TranslateCaptionRepositoryInterface
{
    public function listTranslate($param);

    public function getTranslate($param);

    public function addTranslate($param);

    public function updateTranslate($param);

    public function deleteTranslate($param);
}
