<?php

namespace App\Http\Controllers\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;

class user_controller extends Controller
{
    public function register(Request $request){
        
        $this->validate($request,[
            'phone'      => 'required|regex:/(98)[0-9]{9}/|unique:users,phone',
            'first_name' => 'required|string|',
            'last_name'  => 'required|string',
            'password'   => 'required|string|min:8'
        ]);

        $now = Carbon::now();

        $registration_info = [
            'phone'      => $request->phone,
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'password'   => sha1($request->password),
            'created_at' => $now,
            'updated_at' => $now,
            'role'       => 1,
        ];
        
        DB::table('users')->insert($registration_info);

        return response()->json([
            'status' => true,
            'msg' => 'user registerd successfully.'
        ],201);
    }
}
