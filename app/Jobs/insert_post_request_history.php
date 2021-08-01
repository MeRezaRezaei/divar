<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use DB;
use Carbon\Carbon;
use App\General\general;


class insert_post_request_history implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use general;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $post_id;
    public $user_id;
    public $login_status;
    public $now;
    public function __construct($request)
    {
        $this->login_status = $request->session()->has('user_id');
        if($this->login_status){
            $this->post_id = $request->id;
            $this->user_id = $request->session()->get('user_id');
            $this->now = Carbon::now();
        }
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->login_status){
            

            if($this->is_post_seen_before($this->post_id,$this->user_id)){
                DB::table('post_seens')
                ->where([
                    'user_id' => $this->user_id,
                    'post_id' => $this->post_id,
                ])
                ->update([
                    'updated_at' =>$this->now,
                ])
                ;
            }
            else{
                DB::table('post_seens')
                ->insert([
                    'user_id' => $this->user_id,
                    'post_id' => $this->post_id,
                    'created_at' =>$this->now,
                    'updated_at' =>$this->now,
                ])
                ;
               
            }
        }
        
        
    }
    // public function is_post_seen_before($post_id,$user_id){
    //     $seen_before = DB::table('post_seens')
    //     ->where([
    //         'user_id' => $user_id,
    //         'post_id' => $post_id,
    //     ])
    //     ->first();
    //     return $seen_before ? true : false;
    // }
}
