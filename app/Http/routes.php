<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'api/v1'], function() {

    Route::get('test', 'TestController@index');

    Route::resource('vehicles', 'VehiclesController');

    Route::get('user', 'UserController@show');
    Route::post('user', 'Auth\AuthController@postRegister');
    Route::put('user', 'UserController@update');

    Route::post('auth/login', 'Auth\AuthController@postLogin');
    Route::get('auth/logout', 'Auth\AuthController@getLogout');

    Route::get('services', ['as'=>'api.v1.services.index', 'uses'=>'ServicesController@index']);
    Route::get('services/coords', 'ServicesController@coords');
    Route::get('services/availability', 'ServicesController@availability');
    Route::get('services/{services}', ['as'=>'api.v1.services.show', 'uses'=>'ServicesController@show']);

    Route::get('orders', 'OrdersController@index');
    Route::post('orders', 'OrdersController@store');

    Route::put('orders/{orders}', 'OrdersController@update');    
    Route::get('orders/{orders}', ['as'=>'api.v1.orders.show', 'uses'=>'OrdersController@show']);    

});
