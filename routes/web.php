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
Route::group(['middleware' => ['localization']], function () {

    Route::get('/', 'SiteController@index');

    //Auth::routes();

    Route::get('/contact-us', 'SiteController@contactUs')->name('contact-us');
    Route::get('/checkout', 'SiteController@checkout')->name('checkout');
    Route::get('/thankyou-promos', 'SiteController@upsells')->name('upsells');
    Route::get('/thankyou', 'SiteController@thankyou')->name('thankyou');
    Route::get('/order-tracking', 'SiteController@orderTracking')->name('order-tracking');
    Route::get('/products', 'SiteController@products')->name('products');

    Route::get('/test-bluesnap', 'PaymentsController@testBluesnap');
    Route::get('/test-checkoutcom', 'PaymentsController@testCheckoutCom');
    Route::get('/test-paypal', 'PaymentsController@testPaypal');

    Route::get('/test-confirmation-email', 'EmailController@testConfirmationEmail');
    Route::get('/test-satisfaction-email', 'EmailController@testSatisfactionEmail');

    Route::post('/payment/bluesnap-transaction', 'Payments\BluesnapController@createTransaction')->name('bluesnap-payment');

    Route::get('/test', 'SiteController@test');

});

Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});
