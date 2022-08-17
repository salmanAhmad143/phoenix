<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Client extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $primaryKey = 'clientId';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'clientName', 'address', 'city', 'postalCode', 'country', 'salesRep', 'projectManager', 'projectLead', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'clientId','country','salesRep','projectManager','projectLead'
    ];

    protected $appends = ['id'];

    public function getIdAttribute()
    {
        if (!empty($this->attributes['clientId'])) {
           return Hashids::encode($this->attributes['clientId']);
        }
    }

    public function projectLeadDetail()
    {
        return $this->hasOne('\App\Models\User', 'userId', 'projectLead');
    }

    public function projectManagerDetail()
    {
        return $this->hasOne('\App\Models\User', 'userId', 'projectManager');
    }

    public function salesRepDetail()
    {
        return $this->hasOne('\App\Models\User', 'userId', 'salesRep');
    }

    public function createdBy()
    {
        return $this->hasOne('\App\Models\User', 'userId', 'createdBy');
    }

    public function salesPoc()
    {
        return $this->hasMany('\App\Models\ClientPoc', 'clientId', 'clientId');
    }

    public function operationalPoc()
    {
        return $this->hasMany('\App\Models\ClientOperationalPoc', 'clientId', 'clientId');
    }

    public function countryDetail()
    {
        return $this->hasOne('\App\Models\CodeMaster', 'id', 'country');
    }
}
