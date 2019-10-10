<?php
use Illuminate\Support\Facades\Route;
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
Route::group(['middleware' => ['localization']], function (\Illuminate\Routing\Router $router) {
    $router->get('/', 'SiteController@index');
    $router->get('/contact-us', 'SiteController@contactUs')->name('contact-us');
    $router->get('/returns', 'SiteController@returns')->name('returns');
    $router->get('/privacy', 'SiteController@privacy')->name('privacy');
    $router->get('/terms', 'SiteController@terms')->name('terms');
    $router->get('/about', 'SiteController@about')->name('about');
    $router->get('/splash', 'SiteController@splash')->name('splash');
    $router->get('/checkout', 'SiteController@checkout')->name('checkout');
    $router->get('/thankyou-promos', 'SiteController@upsells')->name('upsells');
    //$router->get('/promo', 'SiteController@promo')->name('promo');
    $router->get('/thankyou', 'SiteController@thankyou')->name('thankyou');
    $router->get('/order-tracking', 'SiteController@orderTracking')->name('order-tracking');
    //$router->get('/products', 'SiteController@products')->name('products');
    $router->get('/product/{product}', 'ProductController@getProduct')->name('ajax.product');
    $router->get('/product', 'ProductController@view')->name('product');
    $router->get('/product/local-price', 'ProductController@getLocalPrice');
    $router->get('/test-bluesnap', 'PaymentsController@testBluesnap');
    $router->post('/test-checkout-card', 'PaymentsController@createCardOrder');
    $router->post('/test-checkout-card-upsells', 'PaymentsController@createCardUpsellsOrder');
    $router->post('/test-checkoutdotcom-captured', 'PaymentsController@checkoutDotComCapturedWebhook');
    $router->post('/test-checkoutdotcom-failed', 'PaymentsController@checkoutDotComFailedWebhook');
    $router->get('/test-paypal', 'PaymentsController@testPaypal');
    //$router->get('/test-confirmation-email', 'EmailController@testConfirmationEmail');
    //$router->get('/test-satisfaction-email', 'EmailController@testSatisfactionEmail');
    //$router->post('/payment/bluesnap-transaction', 'Payments\BluesnapController@createTransaction')->name('bluesnap-payment');
    $router->get('/test', 'SiteController@test');
    //$router->post('/save-txn', 'OrderController@saveTxn');
    //$router->post('/send-order', 'OrderController@saveOrder');
    //$router->post('/save-customer', 'OrderController@saveCustomer');
    $router->post('/payments/bluesnap-generate-token', 'Payments\BluesnapController@generateToken');
    $router->post('/payments/bluesnap-send-transaction', 'Payments\BluesnapController@sendTransaction');
    $router->post('/payments/three', 'Payments\EbanxController@sendTransaction');
    $router->post('/payments/ebanx-notification', 'Payments\EbanxController@notification');
    $router->get('/upsell-product/{productId}', 'ProductController@getUpsellProduct');
	$router->post('/calculate-upsells-total', 'ProductController@calculateUpsellsTotal');
    $router->get('/order-amount-total/{orderId}', 'OrderController@orderAmountTotal');

    // test route
    //$router->get('/test-checkout', 'Payments\PaypalController@checkout');
    $router->post('/paypal-create-order', 'Payments\PaypalController@createOrder');
    $router->post('/paypal-verify-order', 'Payments\PaypalController@verifyOrder');
    $router->post('/paypal-webhooks', 'Payments\PaypalController@webhooks');
});

/*Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});*/