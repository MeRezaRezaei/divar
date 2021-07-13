<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pictures extends Model
{
    protected $fillables = [
        'psot_id',
        'path'
    ];
}
