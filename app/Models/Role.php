<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Role extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "role";

    protected $primaryKey = 'roleId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    protected $appends = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'roleId'
    ];

    public function getIdAttribute()
    {
        if (!empty($this->attributes['roleId'])) {
            return Hashids::encode($this->attributes['roleId']);
        }
    }

    public function permissions()
    {
        return $this->hasMany('App\Models\Permission', 'roleId', 'roleId');
    }

    public function user()
    {
        return $this->hasMany('App\Models\User', 'roleId', 'roleId');
    }
}
