<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name'
    ];
    
    public function attributes(){
        return $this->hasMany('App\Attributes','category_id','id');
    }

    public function category_parent(){
        return $this->belongsTo('App\Category','parent_id','id');
    }

}
