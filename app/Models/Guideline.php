<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Guideline extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "guideline";

    protected $primaryKey = 'guidelineId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'minDuration', 'maxDuration', 'frameGap', 'maxLinePerSubtitle', 'maxCharsPerLine', 'maxCharsPerSecond', 'subtitleSyncAccuracy', 'languageId', 'defaultStatus', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'guidelineId'
    ];

    protected $appends = ['id'];

    public function getIdAttribute()
    {
        if (!empty($this->attributes['guidelineId'])) {
            return Hashids::encode($this->attributes['guidelineId']);
        }
    }

    public function language()
    {
        return $this->belongsTo('App\Models\Language', 'languageId', 'languageId');
    }
}
