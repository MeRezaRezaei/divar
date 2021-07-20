<?php
use App\Http\Middleware\loginCheck;
use App\Http\Middleware\isAdmin;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/register','Accounting\user_controller@register');

Route::post('/login','Accounting\user_controller@login');

Route::group(['middleware'=>loginCheck::class],function(){
    
    Route::post('/logout','Accounting\user_controller@logout');

    Route::post('/userInfo','Advertisement\post_controller@get_user_info');

    Route::post('/CreateNewPost','Advertisement\post_controller@create_new_post');

    Route::post('/savePost','Advertisement\post_controller@save_post');

    Route::post('unsavePost','Advertisement\post_controller@unsave_post');

    Route::post('/seeSaved','Advertisement\post_controller@see_saved');


});

Route::group(['middleware'=>isAdmin::class],function(){

    Route::post('getNotConfirmedPosts','Admin\admin_controller@get_not_confirmed_posts');

    Route::post('confirmePost','Admin\admin_controller@confirm_post');

});

Route::post('/getCategories','Advertisement\post_controller@get_categories');

Route::post('/getPlaces','Advertisement\post_controller@get_places');

Route::post('/searchPosts','Advertisement\post_controller@search_posts');


