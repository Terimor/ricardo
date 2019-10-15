<?php

namespace App\Http\Controllers;

use App\Models\OdinCustomer;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Models\Setting;
use App\Services\I18nService;
use App\Services\OrderService;
use Cache;

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
    public function checkout(Request $request, ProductService $productService)
    {
		$viewTemplate = 'checkout';

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
            'ipqualityscore_api_hash',
            'ebanx_integration_key',
            'ebanx_api_url',
        ));

        $countries =  \Utils::getCountries();

		$loadedPhrases = (new I18nService())->loadPhrases('checkout_page');

        $countryCode = \Utils::getLocationCountryCode();

        $recentlyBoughtData = OdinCustomer::getRecentlyBoughtData();
        $recentlyBoughtNames = $recentlyBoughtData['recentlyBoughtNames'];
        $recentlyBoughtCities = $recentlyBoughtData['recentlyBoughtCities'];

        return view(
            $viewTemplate,
            compact(
                'countryCode', 'product', 'isShowProductOffer', 'setting', 'countries', 'loadedPhrases',
                'recentlyBoughtNames', 'recentlyBoughtCities'
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
            $orderCustomer = OrderService::getCustomerDataByOrderId($request->get('order'));
		}

        if (!$orderCustomer) {
            // generate global get parameters
            $params = \Utils::getGlobalGetParameters($request);
            return redirect('/checkout'.$params);
        }

        $countryCode = \Utils::getLocationCountryCode();

        $loadedPhrases = (new I18nService())->loadPhrases('upsells_page');

        // check affid
        $order_aff = null;
        if ($request->get('affid')) {
            $order_aff = OrderService::getReducedData($request->get('order'), $request->get('affid'));
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
        $countryCode = \Utils::getLocationCountryCode();

        $loadedPhrases = (new I18nService())->loadPhrases('thankyou_page');

        // check affid
        $order_aff = null;
        if ($request->get('affid')) {
            $order_aff = OrderService::getReducedData($request->get('order'), $request->get('affid'));
            $order_aff = $order_aff ? $order_aff->toArray() : null;
        }

        return view('thankyou', compact('countryCode', 'product' , 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff'));
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
    
    public function test(Request $request)
    {
        echo '<pre>'; var_dump(request()->getHost()); echo '</pre>';
        echo '<pre>'; var_dump(request()->server()); echo '</pre>';
    }

}