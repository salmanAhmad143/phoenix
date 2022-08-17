<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Workflow extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "workflow";

    protected $primaryKey = 'workflowId';
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
        'workflowId'
    ];

    protected $appends = ['workflowIdentity'];

    public function getworkflowIdentityAttribute()
    {
        if (!empty($this->attributes['workflowId'])) {
            return Hashids::encode($this->attributes['workflowId']);
        }
    }

    public function workFlow() {
        return $this->hasOne('App\Models\CodeMaster', 'id', 'workflowType');
    }


}
