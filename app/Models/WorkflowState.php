<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowState extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "workflow_state";

    // PRIMARY KEY
    protected $primaryKey = 'stateId';

    protected $fillable = [
        'stateId', 'name', 'workflowId', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];
}
