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

    protected $all_related_attributes_for_new_post = [];

    protected $picture_pathes = [];

    protected $min_pictures_count = 0;

    protected $max_pictures_count = 10;

    protected $acceptable_pictures_extensions = ['png','jpj','jpeg'];

    public function create_new_post(Request $request){
        
        $this->validate_new_post($request);

        $all_pictures = $request->allFiles();
        $pictures_validation_status = $this->validate_pictures($all_pictures);

        if($pictures_validation_status !== true) { 
            return $pictures_validation_status;
        }
        
        $this->store_post_pictures($all_pictures);

        DB::beginTransaction();

        $category_id = $request->category_id;

        try{
            $now = Carbon::now();
            $post_id = $this->insert_new_post_and_get_its_id([
                'subject'     => $request->subject,
                'description' => $request->description,
                'place_id'    => $request->place_id,
                'category_id' => $category_id,
                'price'       => $request->price,
                'is_urgent'   => $request->is_urgent,

                'user_id' => $request->session()->get('user_id'),
                'is_elevated' => false,
                'is_confirmed' => false,
                'created_at' => $now,
                'updated_at' => $now,

            ]);
            
            
            $this->insert_post_pictures($post_id,$this->picture_pathes);

            $attributes_to_insert = $this->find_recived_attributes_from_request($request);
            $this->insert_post_attributes($post_id,$category_id,$attributes_to_insert);

        }
        catch(Exception $db_exception_while_creating_new_post){

            DB::rollback();

            $this->omit_pictures();

            return $this->handle_db_exception($db_exception_while_creating_new_post);
        }

        DB::commit();
        
        return response()->json([
            'status' => true,
            'msg'    => 'post created successfully.',
        ]);

    }  

    protected function find_recived_attributes_from_request(Request $request){
        $all_attributes_from_request = [];
        foreach($this->all_related_attributes_for_new_post as $attribute){
            $attribute_name = $attribute['name'];
            if($request->filled($attribute_name) ){
                $all_attributes_from_request[$attribute_name] = $request->$attribute_name;
            }
            
        }
        return $all_attributes_from_request;
    }

    protected function store_post_pictures($all_pictures){

        foreach($all_pictures as $file){
        $this->picture_pathes[] = $file->store('post_pictures');
        }
        return $this->picture_pathes;
        
        

    }

    protected function insert_new_post_and_get_its_id(array $post_values){

        try{
            return DB::table('posts')->insertGetId($post_values); 
        }
        catch (Exception $createing_post_exception){

            throw new Exception('inserting new post exception',0,$createing_post_exception);
        }
    }

    protected function insert_post_pictures($post_id,$picture_pathes){

        $now = Carbon::now();
        $post_pictures_to_insert = [];
        foreach($picture_pathes as $path){
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
            throw new Exception('inserting pictures exception',0,$inserting_post_picturs_exception);
        }
    }

    protected function insert_post_attributes($post_id,$category_id,array $attribute_values){
        $all_related_attributes_for_this_post 
        = $this->get_all_parent_and_self_attributes_for_given_category($category_id);
        $now = Carbon::now();
        $post_attributes_for_insert = [];
        foreach($all_related_attributes_for_this_post as $attribute){
            $new_attribute = [];
            $attribute_name = $attribute['name'];
            if(array_key_exists($attribute_name,$attribute_values)){
                
                $new_attribute['value'] = $attribute_values[$attribute_name];
                $new_attribute['post_id'] = $post_id;
                $new_attribute['attribute_id'] = $attribute['id'];
                $new_attribute['created_at'] = $now;
                $new_attribute['updated_at'] = $now;

                $post_attributes_for_insert[] = $new_attribute;
            }
            else continue;
            
        }

        try{
            return DB::table('post_attributes')->insert($post_attributes_for_insert);
        }
        catch(Exception $inserting_post_attribute_exception){
            throw new Exception('inserting post attributes exceptin',0,$inserting_post_attribute_exception);
        }
    }

    protected function omit_pictures(){
        foreach($this->picture_paths as $picture){
            $path = 'images/'.$picture;
            
            Storage::delete($path);
            
        }
    }

    protected function validate_new_post(Request $request){

        $this->validate($request,[

            'subject'     => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'place_id'    => 'required|integer|exists:places,id',
            'category_id' => 'required|integer|exists:categories,id',
            'price'       => 'required|integer',
            'is_urgent'   => 'required|bool',
            
        ]);
        
        $this->all_related_attributes_for_new_post 
        = $this->get_all_parent_and_self_attributes_for_given_category($request->category_id);

        $attribute_validate_rules = [];
        foreach($this->all_related_attributes_for_new_post as $attribute) 
            $attribute_validate_rules[$attribute['name']] 
            = $attribute['is_required'] ? 'required|string|max:60' : 'string|max:60'
        ;

        $this->validate($request,$attribute_validate_rules);
    }

    protected function get_all_parent_and_self_attributes_for_given_category($category_id){

        $category_data = new category_data();
        $all_categories_with_attributes = $category_data->get_categories_with_related_attributes();

        return array_merge(
            $all_categories_with_attributes[$category_id]['attributes'],
            $all_categories_with_attributes[$category_id]['parent_attributes']
        );
        
    }

    // todo use laravel validate tools for files
    protected function validate_pictures($all_files){

        $pictures_count = count($all_files);
        if(!($pictures_count >= $this->min_pictures_count && $pictures_count <= $this->max_pictures_count)){
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
            
            $file_extension = $file->extension();
            $extension_flag = true;
            foreach($this->acceptable_pictures_extensions as $extension){
                if($extension != $file_extension){

                    $extension_flag = false;
                }
            }

            if ($extension_flag)
                return response()->json([
                    'status' => false,
                    'msg'    => 'file extension error one or more files extension is not allowed!',
                ])
            ;
            
        }
        return true;
    }

    protected function handle_db_exception($exception){

        Log::error(''.$exception);

        return $this->return_internal_server_error();
    }

    public function get_categories(){

        $category = new category_data();

        return response()->json([
            'status' => true,
            $category->get_categories_with_related_attributes(),
        ]);
    }

    public function get_places(){
        
        $places = new places_data();

        return response()->json([
            'status' => true,
            $places->get_all_places_with_subplaces(),
        ]);
    }
    
    public function save_post(Request $request){
        $this->validate($request,[
            'id' => 'required|integer',
        ]);
        $post = DB::table('posts')
        ->whereId($request->id)
        ->whereNull('deleted_at')
        ->select('id')->first();

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
                    ->join('pictures', 'pictures.post_id','=','posts.id','left')
                    ->join('users','posts.user_id','=','users.id')
                    ->where('saves.user_id','=',$user_id)
                    ->where('is_active','=',true)
                    ->select([
                        'posts.id',
                        'posts.user_id',
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
                    ->get();
        
        $posts = $this->remove_duplicated_posts_and_put_all_pic_pahtes_together($saved);
        $this->sort_posts($posts);
        return response()->json([
            'status' => true,
            'posts' => $posts,
        ]);

    }


    public $post_search_request_options = [
        'is_confirmed',
        'deleted_at',
        'place_id',
        'category_id',
        'is_urgent',
        'min_price',
        'max_price',
    ];
    
    public function search_posts(Request $request){

        $this->validate($request,[
            'place_id' => 'integer|exists:places,id',
            'category_id' => 'integer|exists:categories,id',
            'only_urgent' => 'boolean',
            'min_price' => 'integer|min:0',
            'max_price' => 'integer|min:0',
        ]);

        $where_options = [];
        foreach($this->post_search_request_options as $option){
            if($request->filled($option)){
                $where_options[$option] = $request->$option;
            }
        }

        $posts = $this->get_posts_information($where_options);

        $this->sort_posts($posts,[
            'is_urgent',
            'is_elevated',
            'price',
        ]);
        
        return response()->json([
            'status' => true,
            'posts' => $posts
        ]);
    }

    public function get_posts_information(array $where_fields){

        $where_cluse = $this->get_posts_where_cluse($where_fields);

        $posts = DB::table('posts')
        ->join('pictures', 'pictures.post_id','=','posts.id','left')
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
        return $this->remove_duplicated_posts_and_put_all_pic_pahtes_together($posts);
    }

    public function get_posts_where_cluse($where_fields){
        $where_cluse = [];
        if(array_key_exists('place_id',$where_fields)){
            $where_cluse[] = ['place_id','=',$where_fields['place_id']];
        }
        if(array_key_exists('category_id',$where_fields)){
            $where_cluse[] = ['category_id','=',$where_fields['category_id']];
        }
        if(array_key_exists('only_urgent',$where_fields) && $where_fields['is_urgent'] ){
            $where_cluse[] = ['is_urgent','=',$where_fields['is_urgent']];
        }
        if(array_key_exists('min_price',$where_fields)){
            $where_cluse[] = ['price','>=',$where_fields['min_price']];
        }
        if(array_key_exists('max_price',$where_fields)){
            $where_cluse[] = ['price','<=',$where_fields['max_price']];
        }
        return $where_cluse;
    }

    public function remove_duplicated_posts_and_put_all_pic_pahtes_together($posts){

        $result_posts = [];
        foreach ($posts as $post) {
            

            if( ! array_key_exists($post->id,$result_posts))
            {
                $result_posts[$post->id] = $post;
            }

            $result_posts[$post->id]->paths[] = $post->path;

            unset($result_posts[$post->id]->path);
        }
        

        return $result_posts;
    }

    public function sort_posts(&$posts,$sort_order = []){

        usort($posts,$this->sort_posts_call_back_function($sort_order));
        
    }

    public function sort_posts_call_back_function($sort_order){

        return function($item1,$item2) use($sort_order){

            foreach($sort_order as $filld_name){
                $item1_filld_name = $item1->$filld_name;
                $item2_filld_name = $item2->$filld_name;
                if( $item1_filld_name == $item2_filld_name ){
                    continue;
                }
                return $item1_filld_name < $item2_filld_name ? 1 : -1 ;
            }
            $created_at1 = strtotime($item1->created_at);
            $created_at2 = strtotime($item2->created_at);

            if($created_at1 == $created_at2){
                return 0 ;
            }
            return $created_at1 < $created_at2 ? 1 : -1;
        };
    }

    public function get_user_info(Request $request){
        $this->validate($request,[
            'post_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        $post = DB::table('posts')
                ->where('posts.id', '=',$request->post_id)
                ->whereNull('deleted_at')
                ->join('users','posts.user_id','=','users.id')
                ->select([
                    'users.id',
                    'users.first_name',
                    'users.last_name',
                    'users.phone',
                ])
                ->first();

        if(!$post){
            return response()->json([
                'status' => false,
                'msg' => 'the requested post does not exist or deleted'
            ]);
        }
        if($request->user_id != $post->id){
            return response()->json([
                'status' => false,
                'msg' => 'requested user did not post the post you want to see its user'
            ]);
        }
        
        return response()->json([
            'status' => true,
            'user' => $post,
        ]);
    }

}
