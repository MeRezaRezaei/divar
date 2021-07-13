<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attributes extends Model
{
    protected $fillables = [
        'category_id',
        'name',
        'is_required',
    ];
}
