<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Media extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "media";

    protected $primaryKey = 'mediaId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'workflowId', 'projectId', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'mediaId', 'projectId'
    ];

    protected $appends = ['id'];

    public function getIdAttribute()
    {
        if (!empty($this->attributes['mediaId'])) {
            return Hashids::encode($this->attributes['mediaId']);
        }
    }

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'projectId', 'projectId');
    }

    public function workflow()
    {
        return $this->belongsTo('App\Models\Workflow', 'workflowId', 'workflowId');
    }

    public function language()
    {
        return $this->belongsTo('\App\Models\Language', 'languageId', 'languageId');
    }

    public function mediaUser()
    {
        return $this->hasMany('\App\Models\MediaUser', 'mediaId', 'mediaId');
    }

    public function mediaTeam()
    {
        return $this->hasMany('\App\Models\MediaTeam', 'mediaId', 'mediaId');
    }

    public function mediaTranscript()
    {
        return $this->hasMany('\App\Models\MediaTranscript', 'mediaId', 'mediaId');
    }
}
