<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    protected $fillable = [
        'user_id',
        'place_id',
        'category_id',
        'subject',
        'description',
        'price',
        'is_urgent',
        'is_elevated',
        'is_confirmed',
        'deleted_at',
        'updated_at',
        'created_at',
    ];
}
