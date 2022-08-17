<?php

namespace Modules\Shortcut\Entities\Models;

use Illuminate\Database\Eloquent\Model;

class userShortcut extends Model
{
    protected $primaryKey = 'userShortcutId';
    public $timestamps = false;
    protected $fillable = ['userId', 'shortcutId', 'customShortcut', 'createdBy', 'createdAt'];
}
