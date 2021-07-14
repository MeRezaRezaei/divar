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
            'first_name' => strip_tags($request->first_name),
            'last_name'  => strip_tags($request->last_name),
            'password'   => sha1($request->password),
            'created_at' => $now,
            'updated_at' => $now,
            'role'       => 1,
        ];
        
        DB::table('users')->insert($registration_info);

        return response()->json([
            'status' => true,
            'msg'    => 'user registerd successfully.'
        ],201);
    }

    public function login(Request $request){
        $this->validate($request,[
            'phone'    => 'required|regex:/(98)[0-9]{9}/',
            'password' => 'required|string',
        ]);

        $user = DB::table('users')
        ->where([
            ['phone',    '=',strip_tags($request->phone)],
            ['password', '=',sha1($request->password)]
        ])
        ->first();
        
        if($user){

            session([
                'user_id'    => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'phone'      => $user->phone,
                'role'       => $user->role
            ]);

            return response()->json([
                'status' => true,
                'msg'    => 'login was successfull.',
            ]);
        }

        return response()->json([
            'status' => false,
            'msg'    => 'incorect phone number or password!',
        ],404);
    }

    public function logout(){

        \Session::flush();
        \Session::save();

        return response()->json([
            'status' => true,
            'msg'    => 'logout was successfull',
        ]);
    }
}
