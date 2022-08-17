<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class MediaTranscript extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "media_transcript";

    protected $primaryKey = 'mediaTranscriptId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mediaId', 'languageId', 'minDuration', 'maxDuration', 'frameGap', 'maxLinePerSubtitle', 'maxCharsPerLine', 'maxCharsPerSecond', 'subtitleSyncAccuracy', 'auto', 'autoTranscribeStatus', 'mediaCloudUrl', 'transcriptionProcessCode', 'plainTextFile', 'textBreakBy', 'timeTextFile', 'cost', 'unit', 'currency', 'workflowId', 'workflowStateId', 'linguistId', 'transitionStatus', 'pmApprovalStatus', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'mediaTranscriptId', 'mediaId'
    ];

    protected $appends = ['id'];

    public function getIdAttribute()
    {
        if ($this->attributes['mediaTranscriptId']) {
            return Hashids::encode($this->attributes['mediaTranscriptId']);
        }
    }

    public function media()
    {
        return $this->belongsTo('App\Models\Media', 'mediaId', 'mediaId');
    }

    public function mediaCaption()
    {
        return $this->hasMany('\App\Models\MediaCaption', 'mediaTranscriptId', 'mediaTranscriptId');
    }

    public function language()
    {
        return $this->belongsTo('\App\Models\Language', 'languageId', 'languageId');
    }

    public function linguist()
    {
        return $this->belongsTo('\App\Models\User', 'linguistId', 'userId');
    }
    
    public function workflow()
    {
        return $this->belongsTo('\App\Models\Workflow', 'workflowId', 'workflowId');
    }
    
    public function workflowState()
    {
        return $this->belongsTo('\App\Models\WorkflowState', 'workflowStateId', 'stateId');
    }
}
