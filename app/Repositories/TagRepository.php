<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;

class TagRepository implements TagRepositoryInterface
{
    public function listTag($param)
    {
        $tag = Tag::query();
        if (isset($param['where']) && count($param['where']) > 0) {
            $tag->where($param['where']);
        }
        return $tag->get();
    }

    public function getTag($where)
    {
        return Tag::where($where)->first();
    }

    public function addTag($param)
    {
        return Tag::create($param['indicator']);
    }

    public function updateTag($param)
    {
        //code
    }

    public function deleteTag($where)
    {
        //code
    }
}
