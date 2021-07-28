<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class post_seens extends Model
{
    protected $fillable = [
        'user_id',
        'post_id',
        'is_phone_requested',
        'created_at',
        'updated_at',
    ];
}
