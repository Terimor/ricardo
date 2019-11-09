<?php

namespace App\Http\Controllers;

use App\Models\OdinCustomer;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\PaymentService;
use App\Services\AffiliateService;
use App\Models\Setting;
use App\Services\I18nService;
use App\Services\OrderService;
use App\Constants\PaymentMethods;
use Cache;
use App\Models\OdinOrder;

class SiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Index page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function index(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('index_page');
        $product = $productService->resolveProduct($request, true);        
        return view('index', compact('product', 'loadedPhrases'));
    }

    /**
     * Contacts page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function contactUs(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('contact_page');
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
        $loadedPhrases = (new I18nService())->loadPhrases('returns_page');
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
        $loadedPhrases = (new I18nService())->loadPhrases('privacy_page');
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
        $loadedPhrases = (new I18nService())->loadPhrases('terms_page');
        $product = $productService->resolveProduct($request, true);
        return view('terms', compact('loadedPhrases', 'product'));
    }

     /**
      * About page
      * @param Request $request
      * @param ProductService $productService
      * @return type
      */
     public function about(Request $request, ProductService $productService)
     {
         $loadedPhrases = (new I18nService())->loadPhrases('about_page');
         $product = $productService->resolveProduct($request, true);
         return view('about', compact('loadedPhrases', 'product'));
     }

    /**
     * Order tracking page
     * @return type
     */
    public function orderTracking(Request $request, ProductService $productService)
    {
        $setting['instant_payment_paypal_client_id'] = Setting::getValue('instant_payment_paypal_client_id');

        $loadedPhrases = (new I18nService())->loadPhrases('order_tracking_page');
        $product = $productService->resolveProduct($request, true);
        return view('order_tracking', compact('loadedPhrases', 'product', 'setting'));
    }

    /**
     * Checkout page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function checkout(Request $request, ProductService $productService, $priceSet = null)
    {        
		$viewTemplate = 'checkout';
        
        if (!empty($priceSet)) {
            $request->merge(['cop_id' => $priceSet]);
        }

		if (request()->get('tpl') == 'vmp41') {
			$viewTemplate = 'vmp41';
		}
        if (request()->get('tpl') == 'vmp42') {
            $viewTemplate = 'vmp42';
        }

        $isShowProductOffer = request()->get('tpl') === 'emc1';

        $product = $productService->resolveProduct($request, true);

        $setting = Setting::getValue(array(
            'instant_payment_paypal_client_id',
            'ipqualityscore_api_hash'
        ));

        $countries =  \Utils::getCountries(true);

		$loadedPhrases = (new I18nService())->loadPhrases('checkout_page');

        $langCode = substr(app()->getLocale(), 0, 2);
        $countryCode = \Utils::getLocationCountryCode();

        $setting['payment_methods'] = collect(PaymentService::getPaymentMethodsByCountry($countryCode))->collapse()->all();

        $recentlyBoughtData = OdinCustomer::getRecentlyBoughtData();
        $recentlyBoughtNames = $recentlyBoughtData['recentlyBoughtNames'];
        $recentlyBoughtCities = $recentlyBoughtData['recentlyBoughtCities'];

        $imagesNames = ['safe_payment'];
        $loadedImages = \Utils::getLocalizedImages($imagesNames);

        return view(
            $viewTemplate,
            compact(
                'langCode', 'countryCode', 'product', 'isShowProductOffer', 'setting', 'countries', 'loadedPhrases',
                'recentlyBoughtNames', 'recentlyBoughtCities', 'loadedImages', 'priceSet'
            )
        );
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

		$setting['instant_payment_paypal_client_id'] = Setting::getValue('instant_payment_paypal_client_id');

		$orderCustomer = null;
		if ($request->get('order')) {
            $orderCustomer = OrderService::getCustomerDataByOrderId($request->get('order'), true);
		}

        if (!$orderCustomer) {
            // generate global get parameters
            $params = \Utils::getGlobalGetParameters($request);
            return redirect('/checkout'.$params);
        }

        $countryCode = \Utils::getLocationCountryCode();

        $loadedPhrases = (new I18nService())->loadPhrases('upsells_page');

        // check aff_id
        $order_aff = null;
        $affId = AffiliateService::getAffIdFromRequest($request);
        if ($affId) {
            $order_aff = OrderService::getReducedData($request->get('order'), $affId);
            $order_aff = $order_aff ? $order_aff->toArray() : null;
        }
        return view('uppsells_funnel', compact('countryCode', 'product', 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff'));
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

		$setting['instant_payment_paypal_client_id'] = Setting::getValue('instant_payment_paypal_client_id');

		$orderCustomer = null;
		if ($request->get('order')) {
            $orderCustomer = OrderService::getCustomerDataByOrderId($request->get('order'), true);
		}

        if (!$orderCustomer) {
            // generate global get parameters
            $params = \Utils::getGlobalGetParameters($request);
            return redirect('/checkout'.$params);
        }

        // get payment method from main txn
        $payment_method = [];
        $main_product = $orderCustomer->getMainProduct(false);
        if (!empty($main_product)) {
            $main_txn = $orderCustomer->getTxnByHash($main_product['txn_hash'], false) ?? [];
            if (!empty($main_txn['payment_method']) && PaymentMethods::$list[$main_txn['payment_method']]) {
                $payment_method['name'] = PaymentMethods::$list[$main_txn['payment_method']]['name'];
                $payment_method['logo'] = PaymentMethods::$list[$main_txn['payment_method']]['logo'];
            }
        }

        $countryCode = \Utils::getLocationCountryCode();

        $loadedPhrases = (new I18nService())->loadPhrases('thankyou_page');

        // check aff_id
        $order_aff = null;
        $affId = AffiliateService::getAffIdFromRequest($request);
        if ($affId) {
            $order_aff = OrderService::getReducedData($request->get('order'), $affId);
            $order_aff = $order_aff ? $order_aff->toArray() : null;
        }

        return view('thankyou', compact('countryCode', 'payment_method', 'product' , 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff'));
    }

    /**
     * Splash page
     * @return type
     */
    public function splash(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('splash_page');
        $product = $productService->resolveProduct($request, true);
        return view('splash', compact('loadedPhrases', 'product'));
    }

    /**
     * Prober
     * @param Request $request
     * @return string
     */
    public function prober(Request $request) {
        $pw = 'WGJuhSJtxUEzKZxyx7v2CzeEJFpTuM';
        if ($pw !== $request->get('pw')) {
            return '?';
        }

        $good = 'TUDO BEM';
        $bad = 'ALARME!';
        $ok = 'OK';
        $fail = 'FAIL';
        $result = $bad;
        $redis = $fail;

        //check Redis
        $redisContent = Cache::get('SkuProduct');
        if ($redisContent) {
            $redisValidation = current($redisContent)['name']['en'] ?? null;
            if (!empty($redisValidation)) {
                $redis = $ok;
                $result = $good;
            }
        }

        return view('prober', compact('result', 'redis'));
    }

    /**
     * Log postback to file
     * @param Request $request
     * @return type
     */
    public function logPostback(Request $request) {
        file_put_contents(storage_path("log_postbacks.txt"), json_encode($request->all())."\n", FILE_APPEND);
        return response('Ok', 200);
    }
    
    public function testOrderFlagged(Request $request) {
        $orderId = $request->get('order');
        
        $order = OdinOrder::where('_id', $orderId)->first();
        $order->is_flagged = true;
        $order->save();
        
        $order->is_flagged = false;
        $order->save();
        
        $order = OdinOrder::where('_id', $orderId)->first();
        echo '<pre>'; var_dump($order->toArray()); echo '</pre>';
    }

}
