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
    $router->get('/', 'SiteController@index')->name('home');
    $router->get('/contact-us', 'SiteController@contactUs')->name('contact-us');
    $router->get('/returns', 'SiteController@returns')->name('returns');
    $router->get('/privacy', 'SiteController@privacy')->name('privacy');
    $router->get('/delivery', 'SiteController@delivery')->name('delivery');
    $router->get('/terms', 'SiteController@terms')->name('terms');
    $router->get('/about', 'SiteController@about')->name('about');
    $router->get('/splash', 'SiteController@splash')->name('splash');
    $router->get('/checkout', 'SiteController@checkout')->name('checkout');
    $router->get('/checkout/{priceSet}', 'SiteController@checkout')->name('checkout_price_set');
    $router->get('/thankyou-promos', 'SiteController@upsells')->name('upsells');
    //$router->get('/promo', 'SiteController@promo')->name('promo');
    $router->get('/thankyou', 'SiteController@thankyou')->name('thankyou');
    $router->get('/order-tracking', 'SiteController@orderTracking')->name('order-tracking');
    $router->get('/prober', 'SiteController@prober')->name('prober');
    $router->post('/log-data', 'SiteController@logData')->name('log-data');

    //$router->get('/minishop/products', 'MiniShopController@products')->name('minishop.products');

    //$router->get('/products', 'SiteController@products')->name('products');    
    //$router->get('/product', 'ProductController@view')->name('product');
    $router->get('/product/local-price', 'ProductController@getLocalPrice');
    $router->get('/test-bluesnap', 'PaymentsController@testBluesnap');
    $router->get('/payment-methods-by-country', 'PaymentsController@getPaymentMethodsByCountry');
    $router->post('/pay-by-card', 'PaymentsController@createCardOrder');
    $router->get('/pay-by-card-errors', 'PaymentsController@getCardOrderErrors');
    $router->post('/pay-by-card-upsells', 'PaymentsController@createCardUpsellsOrder');
    $router->post('/checkoutdotcom-captured-webhook', 'PaymentsController@checkoutDotComCapturedWebhook');
    $router->post('/checkoutdotcom-failed-webhook', 'PaymentsController@checkoutDotComFailedWebhook');
    $router->post('/ebanx-webhook', 'PaymentsController@ebanxWebhook');
    $router->post('/bluesnap-webhook', 'PaymentsController@bluesnapWebhook');
    $router->post('/minte-3ds/{orderId}', 'PaymentsController@minte3ds');
    //$router->get('/test-confirmation-email', 'EmailController@testConfirmationEmail');
    //$router->get('/test-satisfaction-email', 'EmailController@testSatisfactionEmail');
    $router->get('/test', 'SiteController@test');
    //$router->post('/save-txn', 'OrderController@saveTxn');
    //$router->post('/send-order', 'OrderController@saveOrder');
    //$router->post('/save-customer', 'OrderController@saveCustomer');
    $router->get('/upsell-product/{productId}', 'ProductController@getUpsellProduct');
	$router->post('/calculate-upsells-total', 'ProductController@calculateUpsellsTotal');
    $router->get('/order-amount-total/{orderId}', 'OrderController@orderAmountTotal');

    $router->get('/product-price', 'ProductController@getProductPrice');
    $router->get('/validate-email', 'EmailController@validateEmail');
    $router->get('/address-by-zip', 'SiteController@getEbanxAddressByZip');
    $router->get('/send-postbacks', 'OrderController@sendPostbacks');

    $router->post('/paypal-create-order', 'Payments\PaypalController@createOrder');
    $router->post('/paypal-verify-order', 'Payments\PaypalController@verifyOrder');
    $router->post('/paypal-webhooks', 'Payments\PaypalController@webhooks');

    /* test routes */
    $router->post('/test-payments', 'PaymentsController@test');
    $router->get('/test-postbacks', 'SiteController@logPostback');
    
    // sitemap
    $router->get('/sitemap.xml', 'SiteController@sitemap');
    
});

/*Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});*/
