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

use App\User;
use Illuminate\Http\Response;

Route::group(['prefix' => 'v1'], function() {

    Route::get('version', 'VersionController@index');

    Route::resource('vehicles', 'VehiclesController');

    Route::get('users', 'UserController@index');
    Route::get('user', 'UserController@show');
    Route::post('user/phone-verify', 'UserController@phoneVerify');
    Route::get('user/authenticated', 'UserController@authenticated');

    Route::get('user/{id}', 'UserController@show');
    Route::post('user', 'Auth\AuthController@postRegister');
    Route::put('user', 'UserController@update');

    Route::post('user/duty', 'UserController@duty');
    Route::post('user/location', 'UserController@location');

    Route::post('auth/login', 'Auth\AuthController@postLogin');
    Route::get('auth/logout', 'Auth\AuthController@getLogout');

    Route::get('services', ['as'=>'v1.services.index', 'uses'=>'ServicesController@index']);
    Route::get('services/coords', 'ServicesController@coords');
    Route::get('services/availability', 'ServicesController@availability');
    Route::get('services/{services}', ['as'=>'v1.services.show', 'uses'=>'ServicesController@show']);

    Route::get('orders', 'OrdersController@index');
    Route::get('orders-all-locations', 'OrdersController@all_locations');
    Route::post('orders', 'OrdersController@store');

    Route::put('orders/{orders}', 'OrdersController@update');    
    Route::get('orders/{orders}', ['as'=>'v1.orders.show', 'uses'=>'OrdersController@show']);

    Route::post('notify/push', 'NotifyController@push');

});
