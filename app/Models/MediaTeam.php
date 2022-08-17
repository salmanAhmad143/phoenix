<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaTeam extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    // TABLE NAME
    protected $table = "media_team";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mediaId', 'teamId', 'createdBy', 'createdAt',
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
