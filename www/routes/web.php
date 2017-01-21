<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/about/mission', 'About\AboutController@mission');
Route::get('/about/vision', 'About\AboutController@vision');

Route::get('/job/index', 'Job\JobController@index');
Route::get('/job/{job}', 'Job\JobController@show');
