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
    return view('index');
});

//Auth::routes();

Route::get('/contact-us', 'SiteController@contactUs')->name('contact-us');
Route::get('/checkout', 'SiteController@checkout')->name('checkout');
Route::get('/order-tracking', 'SiteController@orderTracking')->name('order-tracking');
Route::get('/products', 'SiteController@products')->name('products');

Route::get('/test-bluesnap', 'PaymentsController@testBluesnap');
Route::get('/test-checkoutcom', 'PaymentsController@testCheckoutCom');
