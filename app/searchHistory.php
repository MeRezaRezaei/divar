<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class searchHistory extends Model
{
    protected $fillable = [
        'user_id',
        'place_id',
        'category_id',
        'attribute_id',
        'text',
        'att_min',
        'att_max',
        'value',
        'created_at',
        'updated_at',
    ];
}
