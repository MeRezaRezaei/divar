<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use DB;

class insert_search_history implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $is_user_logged_in ;
    public $now ;
    public $user_id ;
    public $search_values_from_request;
    public $search_attributes_from_request ;
        

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request,$where_options,$attribute_filter_rules)
    {
        $this->is_user_logged_in = $request->session()->has('user_id');
        if($this->is_user_logged_in){
            $this->now = Carbon::now();
            $this->user_id = $request->session()->get('user_id');
            $this->search_values_from_request = $where_options;
            $this->search_attributes_from_request = $attribute_filter_rules;
        }
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->is_user_logged_in){

            $this->search_history_id = DB::table('search_histories')
            ->insertGetId($this->get_search_history_array_to_insert());

            $search_int_attributes_for_insert = [];
            $search_text_attributes_for_insert = [];

            list($search_int_attributes_for_insert,$search_text_attributes_for_insert) 
            = $this->get_attribute_for_insert();
            
            DB::table('search_attributes')
            ->insert($search_int_attributes_for_insert)
            ;
            
            DB::table('search_attributes')
            ->insert($search_text_attributes_for_insert)
            ;


        }
    }

    public function get_attribute_for_insert(){
        $search_int_attributes_for_insert = [];
        $search_text_attributes_for_insert = [];

        foreach($this->search_attributes_from_request as $attribute_id => $attribute){

            $search_attribute_for_insert = [
                'search_history_id'=> $this->search_history_id,
                'attribute_id' => $attribute_id,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]; 
            if($attribute['is_int']){

                if($attribute['min']){
                    $search_attribute_for_insert['min'] = $attribute['min'];
                }
                if($attribute['max']){
                    $search_attribute_for_insert['max'] = $attribute['max'];
                }
                
                $search_int_attributes_for_insert[] = $search_attribute_for_insert;
            }
            else{
                $search_attribute_for_insert['value'] = $attribute['value'];

                $search_text_attributes_for_insert[] = $search_attribute_for_insert;
            }
            
        }
        return [
            $search_int_attributes_for_insert,
            $search_text_attributes_for_insert
        ];
    }

    public function get_search_history_array_to_insert(){
        
        $search_history_for_insert = [
            'user_id' => $this->user_id,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];

        if(array_key_exists('subject',$this->search_values_from_request)){
            $search_history_for_insert['subject'] = $this->search_values_from_request['subject'];
        }
        if(array_key_exists('place_id',$this->search_values_from_request)){
            $search_history_for_insert['place_id'] = $this->search_values_from_request['place_id'];
        }
        if(array_key_exists('category_id',$this->search_values_from_request)){
            $search_history_for_insert['category_id'] = $this->search_values_from_request['category_id'];
        }
        if(array_key_exists('min_price',$this->search_values_from_request)){
            $search_history_for_insert['min_price'] = $this->search_values_from_request['min_price'];
        }
        if(array_key_exists('max_price',$this->search_values_from_request)){
            $search_history_for_insert['max_price'] = $this->search_values_from_request['max_price'];
        }
        if(array_key_exists('is_urgent',$this->search_values_from_request)){
            $search_history_for_insert['is_urgent'] = $this->search_values_from_request['is_urgent'];
        }
        return $search_history_for_insert;
    }
}
