<?php

namespace App\Http\Controllers;

use App\Models\OdinCustomer;
use Illuminate\Http\Request;
use App\Services\CurrencyService;
use App\Services\ProductService;
use App\Models\Currency;
use App\Models\Setting;
use App\Services\I18nService;
use App\Models\OdinOrder;
use App\Services\OrderService;

class SiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Index page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function index(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('product_page');

        $product = $productService->resolveProduct($request, true);

        $setting = Setting::whereIn('key',[
                    'instant_payment_paypal_client_id',
                ])->pluck('value', 'key');

        return view('index', compact('product', 'setting'));
    }

    /**
     * Contacts page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function contactUs(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('checkout_page');
        $product = $productService->resolveProduct($request, true);
        return view('contact_us', compact('loadedPhrases', 'product'));
    }

    /**
     * Returns page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function returns(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('checkout_page');
        $product = $productService->resolveProduct($request, true);
        return view('returns', compact('loadedPhrases', 'product'));
    }

    /**
     * Privacy page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function privacy(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('checkout_page');
        $product = $productService->resolveProduct($request, true);
        return view('privacy', compact('loadedPhrases', 'product'));
    }

    /**
     * Terms page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function terms(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('checkout_page');
        $product = $productService->resolveProduct($request, true);
        return view('terms', compact('loadedPhrases', 'product'));
    }

    /**
     * Order traching page
     * @return type
     */
    public function orderTracking()
    {
        return view('order_tracking');
    }

    /**
     * Checkout page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function checkout(Request $request, ProductService $productService)
    {
		$viewTemplate = 'checkout';

		if (request()->get('tpl') == 'vmp41') {
			$viewTemplate = 'vmp41';
		}

        $isShowProductOffer = request()->get('tpl') === 'emc1';

        $product = $productService->resolveProduct($request, true);
        $setting = Setting::whereIn('key',[
                    'instant_payment_paypal_client_id',
                ])->pluck('value', 'key');

        $countries =  \Utils::getCountries();

		$loadedPhrases = (new I18nService())->loadPhrases('checkout_page');

        $countryCode = \Utils::getLocationCountryCode();

        $notificationData = OdinCustomer::getNotificationData();

        return view($viewTemplate, compact('countryCode', 'product', 'isShowProductOffer', 'setting', 'countries', 'loadedPhrases', 'notificationData'));
    }

    /**
     * Upsells page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function upsells(Request $request, ProductService $productService)
    {
		$product = $productService->resolveProduct($request, true);

		$setting = Setting::whereIn('key',[
			'instant_payment_paypal_client_id',
		])->pluck('value', 'key');

		$orderCustomer = null;
		if (request()->get('order')) {
            $orderCustomer = OrderService::getCustomerDataByOrderId(request()->get('order'));
            if (!$orderCustomer) {
                // generate global get parameters
                $params = \Utils::getGlobalGetParameters($request);
                return redirect('/checkout'.$params);
            }
		}

        $countryCode = \Utils::getLocationCountryCode();

        return view('uppsells_funnel', compact('countryCode', 'product', 'setting', 'orderCustomer'));
    }

    /**
     * Thankyou page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function thankyou(Request $request, ProductService $productService)
    {
		$product = $productService->resolveProduct($request, true);

		$setting = Setting::whereIn('key',[
			'instant_payment_paypal_client_id',
		])->pluck('value', 'key');

		$orderCustomer = null;
		if (request()->get('order')) {
			$orderCustomer = OrderService::getCustomerDataByOrderId(request()->get('order'), true);
            if (!$orderCustomer) {
                // generate global get parameters
                $params = \Utils::getGlobalGetParameters($request);
                return redirect('/checkout'.$params);
            }
		}
        $countryCode = \Utils::getLocationCountryCode();

        return view('thankyou', compact('countryCode', 'product' , 'setting', 'orderCustomer'));
    }

    /**
     * Promo page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function promo(Request $request, ProductService $productService)
    {
        $isShowProductOffer = request()->get('tpl') === 'emc1';

        $product = $productService->resolveProduct($request, true);
        $setting = Setting::whereIn('key',[
                    'instant_payment_paypal_client_id',
                ])->pluck('value', 'key');

        $countries =  \Utils::getCountries();
        $countryCode = \Utils::getLocationCountryCode();
        return view('promo', compact('countryCode', 'product', 'isShowProductOffer', 'setting', 'countries'));
    }

    /**
     *
     * @return type
     */
    public function test(Request $request, ProductService $productService)
    {

        /*$start = microtime(true);
        $location = \Location::get('240d:2:d30b:5600:55ee:f486:1527:27a8');
        echo '<pre>'; var_dump($location); echo '</pre>';
        echo 'Script time: '.(microtime(true) - $start).' sec.';

        echo '123'; exit;*/

	//5d6d166a14dec6079e07d171
	/*$order = OdinOrder::where('_id','5d6d166a14dec6079e07d171')->first();
	$order->status = 'v';
	$i = 6;
	$txns = $order->txns;
	$txns[$i]['status'] = 'axc';
	$txns[] = $txns[$i];
	unset($txns[$i]);
	$order->txns = $txns;
	$order->save();
	echo '<pre>'; var_dump($order); echo '</pre>';

	exit;*/

		/*$price = CurrencyService::calculateWarrantyPrice(20, 49.99);
		$p2 = round(20/100 * 49.99, 2);
		echo '<pre>'; var_dump($price); echo '</pre>';
		echo '<pre>'; var_dump($p2); echo '</pre>';exit;*/


        $product = $productService->resolveProduct($request, true);
echo '<pre>'; var_dump(app()->getLocale()); echo '</pre>';
        echo '<pre>'; var_dump($product); echo '</pre>'; exit;
        $currency = Currency::whereCode('USD')->first();
        echo '<pre>'; var_dump($currency); echo '</pre>'; exit;

        $price = 99.81;
        $exchangedPrice = CurrencyService::getLocalPriceFromUsd($price);
        echo '<pre>'; var_dump($exchangedPrice); echo '</pre>'; exit;

        $odinOrder = new \App\Models\OdinOrder();
        $odinOrder->number = $odinOrder->generateOrderNumber('US');
        $odinOrder->customer_email = 'asdaASDSAD@ccc.a     ';
        echo '<pre>'; var_dump($odinOrder->customer_email); echo '</pre>'; exit;
        //$odinOrder->save();

        return view('index');
    }

}
