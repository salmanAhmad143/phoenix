<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api\v1', 'prefix' => 'v1', 'middleware' => 'auth:api'], function () {
    Route::group(['prefix' => '/shortcut'], function () {
        Route::get('/', 'ShortcutController@index')->middleware(['permissions:shortcut,canView']);
        Route::put('/update', 'ShortcutController@update')->middleware(['permissions:shortcut,canEdit']);
        Route::get('/listAll', 'ShortcutController@listAllShortcut');
    });
});