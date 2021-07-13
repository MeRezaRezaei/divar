<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostAttributes extends Model
{
    protected $fillables = [
        'post_id',
        'attribute_id',
    ];
}
