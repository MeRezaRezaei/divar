<?php

namespace App\Http\Controllers\Advertisement;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\category_data;
use App\Http\Controllers\places_data;

use App\Http\General\errors;

class post_controller extends Controller
{

    use errors;
    public function create_new_post(Request $request){
        
        $this->validate($request,[

            'subject'     => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'place_id'    => 'required|integer|exists:places,id',
            'category_id' => 'required|integer|exists:categories,id',
            'price'       => 'required|integer',
            'is_urgent'   => 'required|bool',
            
        ]);

        $category_id = $request->category_id;

        $category_data = new category_data();
        $all_categories_with_attributes = $category_data->get_categories_with_related_attributes();


        $all_attributes = [];
        $attribute_validate_rules = [];
        $all_parent_and_self_attributes = array_merge(
            $all_categories_with_attributes[$category_id]['attributes'],
            $all_categories_with_attributes[$category_id]['parent_attributes']
        );
        
        foreach($all_parent_and_self_attributes as $attribute) {
            $attribute_validate_rules[$attribute['name']] 
            = $attribute['is_required'] ? 'required|string|max:60' : 'string|max:60'
            ;
            $all_attributes[] = [
                'name' => $attribute['name'],
                'id'   => $attribute['id'],
            ];
        }

        unset($all_parent_and_self_attributes);

        $this->validate($request,$attribute_validate_rules);
        unset($attribute_validate_rules);

        $all_files = $request->allFiles();
        $pictures_count = count($all_files);
        if($pictures_count > 10){
            return response()->json([
                'status' => false,
                'msg'    => 'maximum allowed picture numbers are ten'
            ]);
        }

        foreach($all_files as $file){
            if (!$file->isValid())
                return response()->json([
                    'status' => false,
                    'msg'    => 'files did not uploaded successfully pleaze try again!'
                ])
            ;
            
            $extension = $file->extension();
            if (
                !(
                    $extension == 'png'
                    || $extension == 'jpg'
                    || $extension == 'jpge'
                )
            )
                return response()->json([
                    'status' => false,
                    'msg'    => 'file extension error one or more files extension is not allowed!',
                ])
            ;
            
        }

        $picture_paths = [];
        foreach($all_files as $file){
            $picture_paths[] = $file->store('images');
        }

        $now = Carbon::now();

        DB::beginTransaction();

        try{

            $post_id = DB::table('posts')->insertGetId([
            'subject'     => $request->subject,
            'description' => $request->description,
            'place_id'    => $request->place_id,
            'category_id' => $request->category_id,
            'price'       => $request->price,
            'is_urgent'   => $request->is_urgent,

            'user_id' => $request->session()->get('user_id'),
            'is_elevated' => false,
            'is_confirmed' => false,
            'created_at' => $now,
            'updated_at' => $now,

            ]); 
        }
        catch (Exception $createing_post_exception){
            DB::rollback();

            return $this->handle_db_exception($createing_post_exception,$picture_paths);
        }

        $post_pictures_to_insert = [];
        foreach($picture_paths as $path){
            $post_pictures_to_insert[] = [
                'path' => $path,
                'post_id' => $post_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        try{
            DB::table('pictures')->insert($post_pictures_to_insert);
        }
        catch(Exception  $inserting_post_picturs_exception){
            DB::rollback();

            return $this->handle_db_exception($inserting_post_picturs_exception,$picture_paths);
        }

        $post_attributes_for_insert = [];
        foreach($all_attributes as $attribute){
            $new_attribute = [];
            $attribute_name = $attribute['name'];
            if($request->has($attribute_name)){
                
                $new_attribute['value'] = $request->$attribute_name;
                $new_attribute['post_id'] = $post_id;
                $new_attribute['attribute_id'] = $attribute['id'];
                $new_attribute['created_at'] = $now;
                $new_attribute['updated_at'] = $now;

                $post_attributes_for_insert[] = $new_attribute;
            }
            else continue;
            
        }

        try{
            $result = DB::table('post_attributes')->insert($post_attributes_for_insert);
        }
        catch(Exception $inserting_post_attribute_exception){
            DB::rollback();

            return $this->handle_db_exception($inserting_post_attribute_exception,$picture_paths);
        }

        DB::commit();

        if($result && !isset($createing_post_exception) && !isset($insert_post_attribute_exception) )
        return response()->json([
            'status' => true,
            'msg'    => 'post created successfully.',
        ])
        ;
        
        return response()->json([
            'status' => false,
            'msg'    => 'we were not able to create post! :/',
        ]);

    }  

    protected function handle_db_exception($exception,$picture_paths){

        Log::error(''.$exception);

        $this->delete_lost_post_pictures($picture_paths);

        return $this->return_internal_server_error();
    }

    protected function delete_lost_post_pictures($picture_paths){
        foreach($picture_paths as $picture){
            $path = 'images/'.$picture;
            
            Storage::delete($path);
            
        }
    }

    public function get_categories(){

        $category = new category_data();

        return response()->json($category->get_categories_with_related_attributes());
    }

    public function get_places(){
        
        $places = new places_data();

        return response()->json($places->get_all_places_with_subplaces());
    }
    
    public function save_post(Request $request){
        $this->validate($request,[
            'id' => 'required|integer',
        ]);
        $post = DB::table('posts')->whereId($request->id)->select('id')->first();

        if(!$post){
            return response()->json([
                'status' => false,
                'msg' => 'this post does not exist or deleted',
            ]);
        }

        try {
            $post_id = $post->id;
            $user_id = $request->session()->get('user_id');
            $now = carbon::now();

            $save = DB::table('saves')->where([
                ['post_id','=', $post_id],
                ['user_id','=',$user_id]
            ])->first();
            if(!$save){
                DB::table('saves')->insert([
                    'post_id' => $post_id,
                    'user_id' => $user_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            else{
                DB::table('saves')->where([
                    ['post_id','=', $post_id],
                    ['user_id','=',$user_id]
                ])->update([
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
            }
            
            
        }
        catch(Exception $inserting_into_saves_exception){

            return $this->log_error_and_return_internal_server_error($inserting_into_saves_exception);
        }
        
        return response()->json([
            'status' => true,
            'msg' => 'post saved to your saved posts',
        ]);

    }

    public function unsave_post(Request $request){
        $this->validate($request,[
            'id' => 'required|integer',
        ]);

        $post_id = $request->id;
        $user_id = $request->session()->get('user_id');

        try{
            $save = DB::table('saves')->where([
                'post_id' => $post_id,
                'user_id' => $user_id,
            ])->first();

            if($save){
                
                DB::table('saves')->where([
                    'post_id' => $post_id,
                    'user_id' => $user_id,
                ])->update([
                    'is_active'=>false,
                    'updated_at' => carbon::now(),
                ]);
                
                return response()->json([
                    'status' => true,
                    'msg' => 'post unsaved successfully.'
                ]);
            }
        }
        catch(Exception $deleting_saved_post_exception){
            return $this->log_error_and_return_internal_server_error($deleting_saved_post_exception);
        }
        return response()->json([
            'status' => false,
            'msg' => 'you did not save this post',
        ]);
        

    }

    public function see_saved(Request $request){

        $user_id = $request->session()->get('user_id');
        $saved = DB::table('saves')
        ->join('posts','saves.post_id','=','posts.id')
        ->where('saves.user_id','=',$user_id)
        ->get();

        return response()->json($saved);

    }
    public $all_posts = [];

    public function search_posts(Request $request){

        $this->validate($request,[
            'place_id' => 'integer|exists:places,id',
            'category_id' => 'integer|exists:categories,id',
            'only_urgent' => 'boolean',
            'min_price' => 'integer|min:0',
            'max_price' => 'integer|min:0',
        ]);

        $where_cluse = [
            ['is_confirmed','=',true],
            ['deleted_at','=',null],
        ];
        if($request->filled('place_id')){
            $where_cluse[] = ['place_id','=',$request->place_id];
        }
        if($request->filled('category_id')){
            $where_cluse[] = ['category_id','=',$request->category_id];
        }
        if($request->filled('only_urgent') && ($only_urgent = $request->only_urgent) ){
            $where_cluse[] = ['is_urgent','=',$only_urgent];
        }
        if($request->filled('min_price')){
            $where_cluse[] = ['price','>=',$request->min_price];
        }
        if($request->filled('max_price')){
            $where_cluse[] = ['price','<=',$request->max_price];
        }

        $posts = DB::table('posts')
        ->join('pictures', 'posts.id','=','pictures.post_id')
        ->join('users','posts.user_id','=','users.id')
        ->where($where_cluse)
        ->select([
            'posts.id',
            'user_id',
            'users.first_name',
            'users.last_name',
            'place_id',
            'category_id',
            'subject',
            'description',
            'price',
            'is_urgent',
            'is_elevated',
            'posts.created_at',
            'path'
        ])
        ->get()->toArray();
        ;

        $post_uniqe = [];
        foreach($posts as $post){
            $post_id = $post->id;
            if(!array_key_exists($post_id,$post_uniqe)){
                $post->paths = [];
                $post_uniqe[$post->id] = $post;
            }
        }

        foreach($posts as $post){
            $post_uniqe[$post->id]->paths[] = $post->path;
            unset($post_uniqe[$post->id]->path);
        }
        
        

        usort($post_uniqe,function($item1,$item2){
            $is_urgent1 = $item1->is_urgent;
            $is_urgent2 = $item2->is_urgent;

            if($is_urgent1 == $is_urgent2){

                $is_elevated1 = $item1->is_elevated;
                $is_elevated2 = $item2->is_elevated;

                if($is_elevated1 == $is_elevated2){

                    $price1 = $item1->price;
                    $price2 = $item2->price;

                    if($price1 == $price2){

                        $created_at1 = strtotime($item1->created_at);
                        $created_at2 = strtotime($item2->created_at);

                        if($created_at1 == $created_at2){
                            return 0 ;
                        }
                        return $created_at1 < $created_at2 ? 1 : -1;
                    }
                    return $price1 < $price2 ? 1 : -1;
                }
                return  $is_elevated1 < $is_elevated2 ? 1 : -1;
            }
            return $is_urgent1 < $is_urgent2 ? 1 : -1;
        });
        
        return response()->json($post_uniqe);
    }


    public function get_user_info(Request $request){
        $this->validate($request,[
            'id' => 'required|integer'
        ]);

        $post = DB::table('posts')
        ->where('posts.id', '=',$request->id)
        ->join('users','posts.user_id','=','users.id')
        ->select([
            'users.id',
            'users.first_name',
            'users.last_name',
            'users.phone',
        ])
        ->first();
        
        return response()->json($post);
    }
 

}
