<?php

namespace App\Models;

use App\Http\Traits\Hashidable;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Project extends Model
{
    use Hashidable;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "project";

    protected $primaryKey = 'projectId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'workflowId', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt','workFlowTranslationId', 'projectLeadId', 'projectManagerId', 'note', 'startDate', 'dueDate','clientId'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'projectId', 'workflowId', 'workFlowTranslationId', 'projectLeadId', 'projectManagerId', 'clientId'
    ];

    protected $appends = ['id'];

    public function getIdAttribute()
    {
        if (!empty($this->attributes['projectId'])) {
            return Hashids::encode($this->attributes['projectId']);
        }
    }

    public function media()
    {
        return $this->hasMany('\App\Models\Media', 'projectId', 'projectId');
    }

    public function projectUser()
    {
        return $this->hasMany('\App\Models\ProjectUser', 'projectId', 'projectId');
    }

    public function projectTeam()
    {
        return $this->hasMany('\App\Models\ProjectTeam', 'projectId', 'projectId');
    }

    public function transcription()
    {
        return $this->hasOne('\App\Models\Workflow', 'workflowId', 'workflowId');
    }

    public function translation()
    {
        return $this->hasOne('\App\Models\Workflow', 'workflowId', 'workFlowTranslationId');
    }

    public function projectLead()
    {
        return $this->hasOne('\App\Models\User', 'userId', 'projectLeadId');
    }

    public function projectManager()
    {
        return $this->hasOne('\App\Models\User', 'userId' , 'projectManagerId');
    }

    public function projectTags()
    {
        return $this->hasMany('\App\Models\ProjectTag', 'projectId', 'projectId');
    }

    public function projectClient()
    {
        return $this->hasOne('\App\Models\Client', 'clientId' , 'clientId');
    }
}
