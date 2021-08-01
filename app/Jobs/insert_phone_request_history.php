<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use App\General\general;
use DB;

class insert_phone_request_history implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use general;

    public $is_loged_in;
    public $user_id,$post_id,$now;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->is_loged_in = $request->session()->has('user_id');
        if($this->is_loged_in){
            $this->user_id = $request->session()->get('user_id');
            $this->post_id = $request->post_id;
            $this->now = carbon::now();
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->is_loged_in){

            if($this->is_post_seen_before($this->post_id,$this->user_id)){
                DB::table('post_seens')
                ->where([
                    'user_id' => $this->user_id,
                    'post_id' => $this->post_id,
                ])
                ->update([
                    'is_phone_requested' => true,
                    'updated_at' => $this->now, 
                ]);

            }
            else{

                DB::table('post_seens')
                ->insert([
                    'user_id' => $this->user_id,
                    'post_id' => $this->post_id,
                    'is_phone_requested' => true,
                    'created_at' =>$this->now,
                    'updated_at' =>$this->now,
                ]);

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
