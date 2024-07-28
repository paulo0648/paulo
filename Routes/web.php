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

Route::prefix('flowiseai')->group(function() {
    Route::get('/', 'FlowiseaiController@index');
});



Route::group([
    'middleware' =>[ 'web','impersonate'],
    'namespace' => 'Modules\Flowiseai\Http\Controllers'
], function () {
     //Admin bot
     Route::get('flowisebots', 'AdminbotsController@index')->name('flowisebots.index');
     Route::get('flowisebots/{bot}/edit', 'AdminbotsController@edit')->name('flowisebots.edit');
     Route::get('flowisebots/create', 'AdminbotsController@create')->name('flowisebots.create');
     Route::post('flowisebots', 'AdminbotsController@store')->name('flowisebots.store');
     Route::put('flowisebots/{bot}', 'AdminbotsController@update')->name('flowisebots.update');
     Route::get('flowisebots/del/{bot}', 'AdminbotsController@destroy')->name('flowisebots.delete');

     //Company bot seetup
     Route::get('bots', 'AdminbotsController@indexforcompanyes')->name('flowisebots.indexcompany');
     Route::get('bots/{bot}/config', 'AdminbotsController@config')->name('flowisebots.configure');
     Route::put('bots/{bot}', 'AdminbotsController@updateconfig')->name('flowisebots.updateconfig');

    });
