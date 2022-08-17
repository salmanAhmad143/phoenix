<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOperationalPoc extends Model
{
    const CREATED_AT = 'createdAt';

    protected $primaryKey = 'operationalPocId';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'clientId', 'userId', 'createdBy', 'createdAt'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'clientId','userId'
    ];

    public function pocUserDetail()
    {
        return $this->hasOne('\App\Models\User', 'userId', 'userId');
    }
}
