<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ThirdPartyApiCall extends Authenticatable
{
    use Notifiable;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'third_party_api_call';
    protected $primaryKey = 'thirdPartyAPICallId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mediaTranscriptId', 'url', 'provider', 'request', 'response', 'results', 'confidence', 'requestTime', 'responseTime', 'responseStatus', 'clientIp', 'callingUrl', 'status', 'updatedBy', 'updatedAt',
    ];
}
