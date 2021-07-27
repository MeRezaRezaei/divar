<?php

use App\Category;
use App\Attributes;
use App\Places;
use App\Users;
use Illuminate\Database\Seeder;

class categories_attributes extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = Users::Create([
            "phone"=> "989371030532",
            "first_name"=> "Reza",
            "last_name"=> "Rezaei",
            "password"=> sha1("testtest")
        ]);


        $categories1 = Category::Create([
        'name' => 'RealEstate',
        ]);

        $categories11 = Category::Create([
        'name' => 'apartment',
        'parent_id' => $categories1->id,
        ]);

        $categories111 = Category::Create([
            'name' => 'kitchen',
            'parent_id' => $categories11->id,
            ]);
        
        $categories2 = Category::Create([
            'name' => 'Car',
        ]);


        $categories3 = Category::Create([
            'name' => 'Electronic'
        ]);

        $attributes1 = Attributes::Create([
            'name'=>'balcony_area',
            'category_id' => $categories11->id,
            'is_required' => true,
            'is_int' => true,
        ]);
        $attributes1 = Attributes::Create([
            'name'=>'area',
            'category_id' => $categories111->id,
            'is_required' => false,
            'is_int' => true,
        ]);
        $attributes2 = Attributes::Create([          
            'name'=>'rooms',
            'category_id' => $categories1->id,
            'is_required' => false,
            'is_int' => true,
        ]);
        $attributes3 = Attributes::Create([
            'name'=>'build_year',
            'category_id' => $categories1->id,
            'is_required' => false,
            'is_int' => true,
        ]);
        $attributes4 = Attributes::Create([
            'name'=>'kind',
            'category_id' => $categories1->id,
            'is_required' => false,
            'is_int' => false,
        ]);



        $attributes5 = Attributes::Create([
            'name'=>'kind',
            'category_id' => $categories2->id,
            'is_required' => false,
            'is_int' => false,
        ]);
        $attributes6 = Attributes::Create([
            'name'=>'model',
            'category_id' => $categories2->id,
            'is_required' => false,
            'is_int' => false,
        ]);
        $attributes7 = Attributes::Create([
            'name'=>'color',
            'category_id' => $categories2->id,
            'is_required' => false,
            'is_int' => false,
        ]);
        $attributes8 = Attributes::Create([
            'name'=>'year',
            'category_id' => $categories2->id,
            'is_required' => false,
            'is_int' => true,
        ]);


        $place1 = Places::Create([
            'name'=>'fars'
        ]);

        $place2 = Places::Create([
            'name'=>'shiraz',
            'parent_id' => $place1->id,
        ]);

        $place3 = Places::Create([
            'name'=>'zand',
            'parent_id' => $place2->id,
        ]);

        $place4 = Places::Create([
            'name'=>'marvdasht',
            'parent_id'=> $place1->id,
        ]);

        $place4 = Places::Create([
            'name'=>'meydan aval',
            'parent_id'=> $place4->id,
        ]);


    }
}
