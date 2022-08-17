<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTeam extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    // TABLE NAME
    protected $table = "project_team";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'projectId', 'teamId', 'createdBy', 'createdAt',
    ];

    public function team()
    {
        return $this->belongsTo('App\Models\Team', 'teamId', 'teamId');
    }

    public function teamMember()
    {
        return $this->hasMany('App\Models\TeamMember', 'teamId', 'teamId');
    }
}
