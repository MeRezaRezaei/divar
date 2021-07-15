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
            "password"=> "testtest"
        ]);

        $place1 = Places::Create([
            'name' => 'shiraz'
        ]);

        $categories1 = Category::Create([
        'name' => 'RealEstate',
        ]);
        
        $categories2 = Category::Create([
            'name' => 'Car',
        ]);


        $categories3 = Category::Create([
            'name' => 'Electronic'
        ]);

        $attributes1 = Attributes::Create([
            'name'=>'area',
            'category_id' => $categories1->id,
            'is_required' => false,
        ]);
        $attributes2 = Attributes::Create([          
            'name'=>'rooms',
            'category_id' => $categories1->id,
            'is_required' => false,
        ]);
        $attributes3 = Attributes::Create([
            'name'=>'build_year',
            'category_id' => $categories1->id,
            'is_required' => false,
        ]);
        $attributes4 = Attributes::Create([
            'name'=>'kind',
            'category_id' => $categories1->id,
            'is_required' => false,
        ]);



        $attributes5 = Attributes::Create([
            'name'=>'kind',
            'category_id' => $categories2->id,
            'is_required' => false,
        ]);
        $attributes6 = Attributes::Create([
            'name'=>'model',
            'category_id' => $categories2->id,
            'is_required' => false,
        ]);
        $attributes7 = Attributes::Create([
            'name'=>'color',
            'category_id' => $categories2->id,
            'is_required' => false,
        ]);
        $attributes8 = Attributes::Create([
            'name'=>'year',
            'category_id' => $categories2->id,
            'is_required' => false,
        ]);


    }
}
