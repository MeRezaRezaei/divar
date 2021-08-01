<?php

namespace App\Http\Controllers\Tests;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\test;

class JobTests extends Controller
{
    public function test(){

        test::dispatch();

    }
}
