<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $fillables = [
        'phone',
        'first_name',
        'last_name',
        'password',
        'role',
    ];
}
