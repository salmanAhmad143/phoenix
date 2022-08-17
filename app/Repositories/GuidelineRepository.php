<?php

namespace App\Repositories;

use Carbon\Carbon;

use App\Models\Guideline;
use App\Repositories\Interfaces\GuidelineRepositoryInterface;

class GuidelineRepository implements GuidelineRepositoryInterface
{
    public function listGuideline($param)
    {
        $guideline = Guideline::query();
        $guideline->with('language:languageId,language');
        if (isset($param['where']) && count($param['where']) > 0) {
            $guideline->where($param['where']);
        }
        if (isset($param['orderBy']) && count($param['orderBy']) > 0) {
            foreach ($param['orderBy'] as $column => $direction) {
                $guideline->orderBy($column, $direction);
            }
        }
        return $guideline->paginate($param['size']);
    }

    public function getGuideline($where)
    {
        return Guideline::where($where)->first();
    }

    public function addGuideline($param)
    {
        return Guideline::create($param['indicator']);
    }

    public function updateGuideline($param)
    {
        $guideline = Guideline::where($param['where'])->first();
        foreach ($param['indicator'] as $key => $value) {
            $guideline->$key = $value;
        }
        $guideline->save();
        return $guideline;
    }

    public function updateGuidelines($param)
    {
        Guideline::where($param['where'])->update($param['indicator']);
    }

    public function deleteGuideline($where)
    {
        $guideline = Guideline::where($where);
        if ($guideline !== null) {
            $guideline->delete();
        }
    }
}
