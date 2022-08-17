<?php

namespace App\Repositories;

use App\Models\Content;
use App\Repositories\Interfaces\ContentRepositoryInterface;

class ContentRepository implements ContentRepositoryInterface
{
    public function paginateContent($param)
    {
        $user = Content::query();
        if (isset($param['where']) && count($param['where']) > 0) {
            $user->where($param['where']);
        }
        return $user->paginate($param['size']);
    }

    public function listContent($param)
    {
        $content = Content::query();
        if (isset($param['where']) && count($param['where']) > 0) {
            $content->where($param['where']);
        }
        return $content->get();
    }

    public function getContent($where)
    {
        return Content::where($where)->first();
    }

    public function addContent($param)
    {
        return Content::create($param['indicator']);
    }

    public function updateContent($param)
    {
        $content = Content::where($param['where'])->first();
        foreach ($param['indicator'] as $key => $value) {
            $content->$key = $value;
        }
        $content->save();
        return $content;
    }

    public function deleteContent($where)
    {
        $content = Content::where($where);
        if ($content !== null) {
            $content->delete();
        }
    }
}
