<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attributes extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'is_required',
    ];
}
