<?php

namespace App\Repositories\Interfaces;

interface LanguageRepositoryInterface
{
    public function listLanguage($param);

    public function getLanguage($where);

    public function addLanguage($param);

    public function updateLanguage($param);

    public function deleteLanguage($where);
}
