<?php

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

Route::get('/redis', 'RedisController@index');
Route::resource('/memcache', 'MemCacheController');
Route::get('/upload', function () {
    return view('media_upload');
});
Route::any('/tus/{any?}', 'Api\v1\MediaController@tusUpload')->where('any', '.*');
