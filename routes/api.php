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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('login', 'API\UserController@login');
Route::post('register', 'API\UserController@register');
Route::get('products', 'API\ProductsController@getProducts');
Route::group(['middleware' => 'auth:api'], function(){
Route::post('details', 'API\UserController@details');
Route::post('cart/view', 'API\CartController@viewCart');
Route::post('cart/add', 'API\CartController@addToCart');
Route::post('cart/clear', 'API\CartController@clearCart');
Route::post('cart/remove', 'API\CartController@removeItem');
Route::post('order/create', 'API\OrderController@postOrder');
});
