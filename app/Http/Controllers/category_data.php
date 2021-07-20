<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Cache;

use DB;

class category_data extends Controller
{
    public $all_categories = [];
    public $all_attributes = [];

    public $major_categories = [];

    public $all_in_one_categories_and_attributes = [];

    public function get_categories_with_related_attributes(){
        return Cache::remember('all_categories_with_related_attributes', 15, function()  {
        $categories = DB::table('categories')->select('id','parent_id','name')->get();

        foreach($categories as $key => $category){
            $category_id = $category->id;
            $this->all_categories[++$key] = [
                'name'=>  $category->name,
                'id' => $category_id,
                'parent_id' => $category->parent_id,
            ];
            $all_in_one_categories_and_attributes[$category_id] = [];
        }
            
        ;

        foreach($categories as $key => $category){
            $category_id = $category->id;
            $this->all_in_one_categories_and_attributes[$category_id] = [
                'self' => $this->all_categories[$category_id],
                'parents' => $this->get_all_category_parents($category_id),
                'childs' =>[],
                'parent_attributes' => [],
                'child_attributes' => [],
                'attributes' => [],
            ];

            $category_parent_id = $category->parent_id;
            if($category_parent_id != null){
                $this->all_in_one_categories_and_attributes
                [$category_parent_id]['childs'][$category_id] 
                = &$this->all_in_one_categories_and_attributes[$category_id]
                ;
            }
            else{
                $this->major_categories[$category_id]
                = &$this->all_in_one_categories_and_attributes[$category_id];
            }
                
            
        }

        $attributes = DB::table('attributes')->select('id','category_id','name','is_required')->get();

        foreach($attributes as $key => $attribute)
            $this->all_attributes[++$key] = [
                'name' => $attribute->name,
                'id' => $attribute->id,
                'category_id' => $attribute->category_id,
                'is_required' => $attribute->is_required,

            ]
        ;
        
        foreach($attributes as $attribute){
            $attribute_id = $attribute->id;

            $this->all_in_one_categories_and_attributes
            [$attribute->category_id]['attributes'][$attribute_id]
            =  &$this->all_attributes[$attribute_id];
        }
        
        foreach($this->all_in_one_categories_and_attributes as $layer_key => $layer){

            foreach($layer['parents'] as $parent_key => $item){
                foreach($this->all_in_one_categories_and_attributes[$parent_key]['attributes'] as $parent_attribute){
                    $parent_attribute_id = $parent_attribute['id'];

                    $this->all_in_one_categories_and_attributes
                    [$layer_key]['parent_attributes'][$parent_attribute_id]
                    = &$this->all_attributes[$parent_attribute_id];
                }
            }

            foreach($layer['childs'] as $child_key => $item){
                foreach($this->all_in_one_categories_and_attributes[$child_key]['attributes'] as $child_attribute){
                    $child_attribute_id = $child_attribute['id'];

                    $this->all_in_one_categories_and_attributes
                    [$layer_key]['child_attributes'][$child_attribute_id]
                    = &$this->all_attributes[$child_attribute_id];
                }
            }

        }

        $this->all_in_one_categories_and_attributes['major_categories'] = $this->major_categories;

        return $this->all_in_one_categories_and_attributes;
        });
        
    }

    public $category_parents = [];

    public function get_all_category_parents($category_id){
        $this->category_parents = [];
        $this->get_category_parent($category_id);
        return $this->category_parents;
    }

    public function get_category_parent($category_id){
        $category = $this->all_categories[$category_id];
        $category_parent_id = $category['parent_id'];
        
        if($category_parent_id == null){
            return;
        }
        else{

            $this->category_parents[$category_parent_id] 
            = &$this->all_categories[$category_parent_id];

            $this->get_category_parent($category_parent_id);
        }

        
    }

}
