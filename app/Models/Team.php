<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Team extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "team";

    protected $primaryKey = 'teamId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'teamId'
    ];

    protected $appends = ['id'];

    public function getIdAttribute()
    {
        if (!empty($this->attributes['teamId'])) {
            return Hashids::encode($this->attributes['teamId']);
        }
    }
}
