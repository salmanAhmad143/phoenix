<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowProcess extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "workflow_process";

    // PRIMARY KEY
    protected $primaryKey = 'processId';

    protected $fillable = [
        'workflowId', 'currentStateId', 'nextStateId', 'transitionId', 'currentStateStatus', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    public function workflowTransition()
    {
        return $this->belongsTo('App\Models\WorkflowTransition', 'transitionId', 'transitionId');
    }
}
