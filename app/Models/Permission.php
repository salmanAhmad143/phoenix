<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    // TABLE NAME
    protected $table = "permission";

    protected $primaryKey = 'permissionId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'roleId', 'contentId', 'canView', 'canAdd', 'canEdit', 'canDelete', 'canDownload', 'status', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt',
    ];

    public function content()
    {
        return $this->belongsTo('App\Models\Content', 'contentId', 'contentId');
    }
}
