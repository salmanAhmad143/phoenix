<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTag extends Model
{
    // TABLE NAME
    protected $table = "project_tags";
    protected $primaryKey = 'projectTagId';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'projectId', 'tagId'
    ];

    public function TagData()
    {
        return $this->hasOne('\App\Models\Tag', 'tagId', 'tagId');
    }
}
