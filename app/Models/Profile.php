<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "profile";

    protected $primaryKey = 'profileId';

    protected $fillable = [
        'secondaryEmail', 'age', 'gender', 'primaryMobileNo', 'secondaryMobileNo', 'countryId', 'stateId', 'cityId', 'address', 'pincode', 'userId', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    public function userLogin()
    {
        return $this->belongsTo('App\Models\User', 'userId');
    }
}
