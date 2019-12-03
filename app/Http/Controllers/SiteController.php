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
use App\Models\Domain;
use App\Http\Requests\ZipcodeRequest;
use App\Services\EbanxService;

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
        //get domain and check views logic
        $domain = Domain::getByName();
        $isMultiproduct = false;
        if (!empty($domain->is_multiproduct) || !empty($domain->is_catch_all)) {
            if (!empty($domain->is_catch_all)) {
                $products = $productService->getAllSoldDomainsProducts($request->get('page'));  
                $isMultiproduct = true;
            } else {
                $products = ProductService::getDomainProducts($domain);
                if ($products && count($products) > 0) {
                    $isMultiproduct = true;
                }                
            }
        }
        
        if (!$isMultiproduct) {            
            return $this->indexSite($request, $productService, $domain);
        } else {
            return $this->indexMiniShop($request, $productService, $domain, $products);
        }
    }
    
    /**
     * index for site logic
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    private function indexSite(Request $request, ProductService $productService, $domain)
    {
        //generatePageTitle
        $loadedPhrases = (new I18nService())->loadPhrases('index_page');
        $product = $productService->resolveProduct($request, true);
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), '');
        return view('index', compact('product', 'loadedPhrases', 'page_title'));
    }
    
    /**
     * Index for minishop logic
     * @param Request $request
     * @param ProductService $productService
     * @param Domain $domain
     * @param array $products
     * @return type
     */
    private function indexMinishop(Request $request, ProductService $productService, $domain, array $products)
    {        
        $product = $productService->resolveProduct($request, true);
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), '');
        return view('minishop/pages/home', compact('products', 'page_title'));
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
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('contact_title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view('contact_us', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'main_logo'));
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
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('refunds_title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view('returns', compact('loadedPhrases', 'product', 'page_title', 'main_logo'));
    }

    /**
     * Delivery page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function delivery(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('delivery_page');
        $product = $productService->resolveProduct($request, true);
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('delivery_title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view('delivery', compact('loadedPhrases', 'product', 'page_title', 'main_logo'));
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
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('privacy_title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        $website_name = $domain->getWebsiteName($product, $request->get('cop_id'), $request->get('product'));
        return view('privacy', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'website_name'));
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
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('terms_title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        $website_name = $domain->getWebsiteName($product, $request->get('cop_id'), $request->get('product'));
        return view('terms', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'website_name'));
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
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('about_title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view('about', compact('loadedPhrases', 'product', 'page_title', 'main_logo'));
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
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('tracking.order_tracking'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view('order_tracking', compact('loadedPhrases', 'product', 'setting', 'page_title', 'main_logo'));
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

        $recentlyBoughtData = OdinOrder::getRecentlyBoughtData();
        $recentlyBoughtNames = $recentlyBoughtData['recentlyBoughtNames'];
        $recentlyBoughtCities = $recentlyBoughtData['recentlyBoughtCities'];

        $imagesNames = ['safe_payment'];
        $loadedImages = \Utils::getLocalizedImages($imagesNames);
        
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('checkout.page_title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view(
            $viewTemplate,
            compact(
                'langCode', 'countryCode', 'product', 'isShowProductOffer', 'setting', 'countries', 'loadedPhrases',
                'recentlyBoughtNames', 'recentlyBoughtCities', 'loadedImages', 'priceSet', 'page_title', 'main_logo'
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
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('upsells.title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view('uppsells_funnel', compact('countryCode', 'product', 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff', 'page_title', 'main_logo'));
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
                $payment_method['logo'] = \Utils::getCdnUrl(true).PaymentMethods::$list[$main_txn['payment_method']]['logo'];
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
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('thankyou_title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view('thankyou', compact('countryCode', 'payment_method', 'product' , 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff', 'page_title', 'main_logo'));
    }

    /**
     * Splash page
     * @return type
     */
    public function splash(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('splash_page');
        $product = $productService->resolveProduct($request, true);
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), '');
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        return view('splash', compact('loadedPhrases', 'product', 'page_title', 'main_logo'));
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
    
    /**
     * Get address by zip code using Ebanx
     * @param Request $request
     * @param EbanxService $ebanxService
     * @return type
     */
    public function getEbanxAddressByZip(ZipcodeRequest $request, EbanxService $ebanxService)
    {                
        return response()->json($ebanxService->getAddressByZip($request->get('zipcode')));
    }    
}
