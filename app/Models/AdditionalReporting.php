<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalReporting extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "additional_reporting";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'userId', 'reportingManager', 'status', 'createdAt', 'updatedAt',
    ];

    public function reportingUser()
    {
        return $this->hasOne('App\Models\User', 'userId', 'reportingManager')->select('userId','name','email');
    }
}
