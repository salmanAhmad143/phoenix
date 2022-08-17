<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;


class User extends Authenticatable
{
    use Notifiable;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $primaryKey = 'userId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'emailVerifiedAt', 'password', 'api_token', 'roleId', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt', 'accessLevelId', 'reportingManagerId', 'departmentId',
    ];

    protected $appends = ['id'];

    public function getIdAttribute()
    {
        if (!empty($this->attributes['userId'])) {
            return Hashids::encode($this->attributes['userId']);
        }
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'api_token', 'userId', 'roleId', 'accessLevelId', 'departmentId', 'reportingManagerId'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->hasOne('App\Models\Profile', 'userId');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'roleId');
    }

    public function reportingManager()
    {
        return $this->hasOne('App\Models\User', 'userId', 'reportingManagerId');
    }

    public function additionalReporting()
    {
        return $this->hasMany('App\Models\AdditionalReporting', 'userId', 'userId');
    }

    public function reportingUsers()
    {
        return $this->hasMany('App\Models\AdditionalReporting', 'reportingManager', 'userId');
    }

    public function accessLevel() {
        return $this->hasOne('App\Models\CodeMaster', 'id', 'accessLevelId');
    }

    public function department() {
        return $this->hasOne('App\Models\CodeMaster', 'id', 'departmentId');
    }
}
