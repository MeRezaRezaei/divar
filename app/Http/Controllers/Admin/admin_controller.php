<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Http\General\errors;

class admin_controller extends Controller
{
    use errors;

    public function get_not_confirmed_posts(Request $request){
        $posts = DB::table('posts')
        ->where('is_confirmed',false)
        ->select('description','subject','id')
        ->get();

        return response()->json($posts);
    }
    
    public function confirm_post(Request $request){
        
        $this->validate($request,[
            'id' => 'required|integer',
        ]);

        try{
            DB::table('posts')->whereId($request->id)->update([
                'is_confirmed' => true,
            ]);
        }
        catch(Exception $finding_post_exception){
            return $this->log_error_and_return_internal_server_error($finding_post_exception);
        }
        
        return response()->json([
            'status' => true,
            'msg' => 'post confirmed successfully.'
        ]);
    }
}
