<?php 
namespace App\General;

use DB;

trait general {
    public function is_post_seen_before($post_id,$user_id){
        $seen_before = DB::table('post_seens')
        ->where([
            'user_id' => $user_id,
            'post_id' => $post_id,
        ])
        ->first();
        return $seen_before ? true : false;
    }
}