<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Saves extends Model
{
    protected $fillables = [
        'user_id',
        'post_id',
    ];
}
