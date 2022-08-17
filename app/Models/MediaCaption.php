<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class MediaCaption extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "media_caption";

    protected $primaryKey = 'mediaCaptionId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mediaTranscriptId', 'startTime', 'endTime', 'sourceText', 'targetText', 'completeStatus', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'mediaCaptionId'
    ];

    protected $appends = ['id'];

    public function getIdAttribute()
    {
        if (!empty($this->attributes['mediaCaptionId'])) {
            return Hashids::encode($this->attributes['mediaCaptionId']);
        }
    }
}
