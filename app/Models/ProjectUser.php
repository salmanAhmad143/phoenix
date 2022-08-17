<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    // TABLE NAME
    protected $table = "project_user";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'projectId', 'userId', 'createdBy', 'createdAt',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'userId', 'userId');
    }
}
