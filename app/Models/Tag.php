<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    // TABLE NAME
    public $timestamps = false;
    protected $table = "tags";
    protected $primaryKey = 'tagId';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tag', 'status', 'createdAt', 'updatedAt'
    ];
}
