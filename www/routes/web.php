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

// Auth
Auth::routes();

// Home
Route::get('/home',                             'HomeController@user')->middleware('auth');
Route::get('/home/user',                        'HomeController@user')->middleware('auth');
Route::get('/home/admin',                       'HomeController@admin')->middleware('admin');

// About
Route::get('/about/mission',                    'About\AboutController@mission');
Route::get('/about/vision',                     'About\AboutController@vision');

// Job search
Route::get('/job/search',                       'Job\JobController@find');
Route::get('/job/search/map',                   'Job\JobController@findOnMap');

// Job
Route::get('/job/index',                        'Job\JobController@index');
Route::get('/job/index/map',                    'Job\JobController@map');
Route::get('/job/{job}/edit',                   'Job\JobController@edit')->middleware('admin');
Route::patch('/job/{job}',                      'Job\JobController@update')->middleware('admin');
Route::get('/job/{job}',                        'Job\JobController@show');
Route::post('/job/{job}/note',                  'Note\NoteController@store');


// Note
Route::get('/note/{note}/edit',                 'Note\NoteController@edit')->middleware('auth');
Route::get('/note/{note}/turnOffListing',       'Note\NoteController@turnOffListing')->middleware('auth');
Route::get('/note/{note}/turnOnListing',        'Note\NoteController@turnOnListing')->middleware('auth');
Route::get('/note/{note}/delete',               'Note\NoteController@delete')->middleware('auth');
Route::patch('/note/{note}',                    'Note\NoteController@update')->middleware('auth');
