<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    // TABLE NAME
    protected $table = "team_member";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'teamId', 'userId', 'createdBy', 'createdAt',
    ];

    public function member()
    {
        return $this->belongsTo('App\Models\User', 'userId', 'userId');
    }
}
