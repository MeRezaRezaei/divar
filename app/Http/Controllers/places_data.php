<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class places_data extends Controller
{

    public $places = [];

    public function get_all_places_with_subplaces(){
        return Cache::remember('all_places_with_subplaces', 15, function()  {
        $places = DB::table('places')->select('id','parent_id','name')->get();
        
        foreach($places as $place){
            $place_id = $place->id;
            $this->places[$place_id] = [
                'name' => $place->name,
                'parent_id' => $place->parent_id,
                'id' => $place_id,
                'child' => [],
            ];
        }
        
        foreach($this->places as $place){
            $place_parent_id = $place['parent_id'];
            $place_id = $place['id'];
            if($place_parent_id){
                $this->places[$place_parent_id]['child'][$place_id] 
                = &$this->places[$place_id];
            }
            else{
                $this->places['major_places'][$place_id] 
                = &$this->places[$place_id];
            }
        }
        
        return $this->places;
        });

    }
}
