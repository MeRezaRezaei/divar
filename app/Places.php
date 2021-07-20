<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Places extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'created_at',
        'updated_at',
    ];
}
