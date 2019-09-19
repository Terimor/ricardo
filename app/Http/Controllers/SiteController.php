<?php

namespace App\Http\Controllers;

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
     * Show the application index.
     *
     * @return \Illuminate\Contracts\Support\Renderable
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
     * Show the application Contact us.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function contactUs()
    {
        return view('contact_us');
    }

    /**
     * Show the application Order tracking.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function orderTracking()
    {
        return view('order_tracking');
    }

    /**
     * Show the application Checkout.
     *
     * @return \Illuminate\Contracts\Support\Renderable
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

        return view($viewTemplate, compact('countryCode', 'product', 'isShowProductOffer', 'setting', 'countries', 'loadedPhrases'));
    }

    /**
     * Show the application Checkout.
     *
     * @return \Illuminate\Contracts\Support\Renderable
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
     * Show the application Checkout.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function thankyou(Request $request, ProductService $productService)
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

        return view('thankyou', compact('countryCode', 'product' , 'setting', 'orderCustomer'));
    }

    /**
     * Show the application Promo.
     *
     * @return \Illuminate\Contracts\Support\Renderable
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
     * Show the application Product.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function products()
    {
        return view('products');
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
