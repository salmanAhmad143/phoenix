<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    // TABLE NAME
    protected $table = "language_master";

    protected $primaryKey = 'languageId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'language', 'languageCode', 'languageStandard', 'region', 'regionCode', 'regionStandard', 'languageFor', 'autoTranslate', 'autoTranscribe',
    ];
}
