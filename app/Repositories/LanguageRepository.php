<?php

namespace App\Repositories;

use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;

class LanguageRepository implements LanguageRepositoryInterface
{
    public function listLanguage($param)
    {
        $language = Language::query();
        if (isset($param['where']) && count($param['where']) > 0) {
            $language->where($param['where']);
        }
        if (isset($param['orWhere']) && count($param['orWhere']) > 0) {
            $language->orWhere($param['orWhere']);
        }
        return $language->get();
    }

    public function getLanguage($where)
    {
        return Language::where($where)->first();
    }

    public function addLanguage($param)
    {
        return Language::create($param['indicator']);
    }

    public function updateLanguage($param)
    {
        $language = Language::where($param['where'])->first();
        foreach ($param['indicator'] as $key => $value) {
            $language->$key = $value;
        }
        $language->save();
        return $language;
    }

    public function deleteLanguage($where)
    {
        $language = Language::where($where);
        if ($language !== null) {
            $language->delete();
        }
    }
}
