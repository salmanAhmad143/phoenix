<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowTransition extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "workflow_transition";

    // PRIMARY KEY
    protected $primaryKey = 'transitionId';

    protected $fillable = [
        'name', 'workflowId', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];
}
