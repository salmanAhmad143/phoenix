<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class CodeMaster extends Model
{
    
    protected $table = 'code_master';
    
    public $timestamps = false;
    
    protected $fillable = ['codeType', 'codeValue', 'sortOrder'];

    protected $appends = ['rowId'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id'
    ];

    public function getRowIdAttribute()
    {
        if (!empty($this->attributes['id'])) {
            return Hashids::encode($this->attributes['id']);
        }
    }
    
}
