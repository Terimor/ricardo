<?php

namespace App\Http\Controllers;

use App\Models\OdinCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Services\ProductService;
use App\Services\PaymentService;
use App\Services\AffiliateService;
use App\Models\Setting;
use App\Models\PaymentApi;
use App\Services\I18nService;
use App\Services\OrderService;
use App\Services\ViacepService;
use App\Services\TemplateService;
use App\Constants\PaymentMethods;
use Cache;
use App\Models\OdinOrder;
use App\Models\Domain;
use App\Http\Requests\ZipcodeRequest;

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
                $products = $productService->getAllSoldDomainsProducts($domain, $request->get('page'), $request->get('search'));
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
        (new I18nService())->loadPhrases('minishop_page');
        $product = $productService->resolveProduct($request, true);
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), '');
        $website_name = $domain->getDisplayedName();
        $is_catch_all = $domain->is_catch_all;
        $cdn_url = \Utils::getCdnUrl();
        $pagination = null;
        if (isset($products['total'])) {
            $pagination = $products;
            $products = $products['products'];
            unset($pagination['products']);
        }
        return view('minishop/pages/home', compact('products', 'page_title', 'website_name', 'is_catch_all', 'cdn_url', 'pagination'));
    }

    /**
     * Product page
     * @param Request $request
     * @param ProductService $productService
     * @param Domain $domain
     * @param array $products
     * @return type
     */
    public function product(Request $request, ProductService $productService)
    {
        $domain = Domain::getByName();
        $cdn_url = \Utils::getCdnUrl();
        (new I18nService())->loadPhrases('minishop_page');
        $product = $productService->resolveProduct($request, true);
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), '');
        return view('minishop/pages/product', compact('cdn_url', 'page_title', 'product'));
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
        $placeholders = TemplateService::getCompanyData($domain);
        $website_name = $domain->getWebsiteName($product, $request->get('cop_id'), $request->get('product'));
        return view('contact_us', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'main_logo', 'website_name', 'placeholders'));
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
        $website_name = $domain->getWebsiteName($product, $request->get('cop_id'), $request->get('product'));
        $placeholders = TemplateService::getCompanyData($domain);
        return view('returns', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'website_name', 'placeholders'));
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
        $website_name = $domain->getWebsiteName($product, $request->get('cop_id'), $request->get('product'));
        $placeholders = TemplateService::getCompanyData($domain);
        return view('delivery', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'website_name', 'placeholders'));
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
        $placeholders = TemplateService::getCompanyData($domain);
        return view('privacy', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'website_name', 'placeholders'));
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
        $placeholders = TemplateService::getCompanyData($domain);
        return view('terms', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'website_name', 'placeholders'));
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
        $payment_api = PaymentApi::getActivePaypal();
        $setting['instant_payment_paypal_client_id'] = $payment_api->key ?? null;

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
     * @return mixed
     */
    public function checkout(Request $request, ProductService $productService, $priceSet = null)
    {
        $is_virtual_product = Route::is('checkout_vrtl');
		$viewTemplate = !$is_virtual_product ? 'checkout' : 'new.pages.vrtl.checkout.templates.vc1';

        if (!empty($priceSet)) {
            $request->merge(['cop_id' => $priceSet]);
        }

        if (!$is_virtual_product) {
    		if ($request->get('tpl') == 'vmp41') {
    			$viewTemplate = 'vmp41';
    		}
            if ($request->get('tpl') == 'vmp42') {
                $viewTemplate = 'vmp42';
            }
            if ($request->get('tpl') == 'fmc5x') {
                $viewTemplate = 'new.pages.checkout.templates.fmc5';
            }
        } else {
            if ($request->get('tpl') == 'vc1') {
                $viewTemplate = 'new.pages.vrtl.checkout.templates.vc1';
            }
            if ($request->get('tpl') == 'vc2') {
                $viewTemplate = 'new.pages.vrtl.checkout.templates.vc2';
            }
        }

        $product = $productService->resolveProduct($request, true);
        $upsells = $productService->getProductUpsells($product);

        $setting = Setting::getValue([
            'ipqualityscore_api_hash',
            'support_address'
        ]);
        $payment_api = PaymentApi::getActivePaypal();
        $setting['instant_payment_paypal_client_id'] = $payment_api->key ?? null;

        $countries =  \Utils::getShippingCountries(true, $product->is_europe_only, $product->countries);

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
        $website_name = $domain->getWebsiteName($product, $request->get('cop_id'), $request->get('product'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));

        $company_address = TemplateService::getCompanyAddress($setting['support_address'], $domain);
        $company_descriptor_prefix = \Utils::getCompanyDescriptorPrefix($request);

        $cdn_url = \Utils::getCdnUrl();

        $deals = [];
        $deal_promo = null;
        $deals_main_quantities = [];
        $deals_free_quantities = [];

        $is_new_engine = ((Route::is('checkout') || Route::is('checkout_price_set')) && $request->get('tpl') === 'fmc5x') || $is_virtual_product;
        if ($is_new_engine) {
            $data_deals = TemplateService::getDealsData($product, $request);
            $deals = $data_deals['deals'];
            $deal_promo = $data_deals['deal_promo'];
            $deals_main_quantities = $data_deals['deals_main_quantities'];
            $deals_free_quantities = $data_deals['deals_free_quantities'];
        }

        return view(
            $viewTemplate,
            compact(
                'langCode', 'countryCode', 'product', 'setting', 'countries', 'loadedPhrases',
                'recentlyBoughtNames', 'recentlyBoughtCities', 'loadedImages', 'priceSet', 'page_title', 'main_logo',
                'company_address', 'company_descriptor_prefix', 'cdn_url', 'upsells', 'website_name',
                'deals', 'deal_promo', 'deals_main_quantities', 'deals_free_quantities'
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
        $is_virtual_product = Route::is('upsells_vrtl');
        $viewTemplate = !$is_virtual_product ? 'uppsells_funnel' : 'new.pages.vrtl.upsells';
		$product = $productService->resolveProduct($request, true);

        $payment_api = PaymentApi::getActivePaypal();
        $setting['instant_payment_paypal_client_id'] = $payment_api->key ?? null;

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
        return view($viewTemplate, compact('countryCode', 'product', 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff', 'page_title', 'main_logo'));
    }

    /**
     * Thankyou page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function thankyou(Request $request, ProductService $productService)
    {
        $is_virtual_product = Route::is('thankyou_vrtl');
        $viewTemplate = !$is_virtual_product ? 'thankyou' : 'new.pages.vrtl.thankyou';
		$product = $productService->resolveProduct($request, true);

        $payment_api = PaymentApi::getActivePaypal();
        $setting['instant_payment_paypal_client_id'] = $payment_api->key ?? null;

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
        return view($viewTemplate, compact('countryCode', 'payment_method', 'product' , 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff', 'page_title', 'main_logo'));
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

        $setting = Setting::getValue([
            'prober_firing_percent',
            'prober_firing_orders',
            'prober_txns_percent',
            'prober_txns_orders'
        ]);

        //check Redis
        $redisContent = Cache::get('SkuProduct');
        if ($redisContent) {
            $redisValidation = current($redisContent)['name']['en'] ?? null;
            if (!empty($redisValidation)) {
                $redis = $ok;
                $result = $good;
            }
        }

        $txns = OrderService::getLastOrdersTxnSuccessPercent((int)$setting['prober_txns_orders'], (float)$setting['prober_txns_percent']);

        if ($txns <= (float)$setting['prober_txns_percent']) {
            $result = $bad;
        }
        $txns.= '%';

        $firing = OrderService::getLastOrdersFiringPercent((int)$setting['prober_firing_orders'], (float)$setting['prober_firing_percent']);

        if ($firing <= (float)$setting['prober_firing_percent']) {
            $result = $bad;
        }
        $firing.= '%';

        return view('prober', compact('result', 'redis', 'txns', 'firing', 'setting'));
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
     * Get Brazillian address by zip code
     * @param Request $request
     * @return type
     */
    public function getBrazilAddressByZip(ZipcodeRequest $request)
    {
        $zipcode = $request->get('zipcode');
        return response()->json(ViacepService::findByZip($zipcode));
    }

    /**
     * Generate sitemap
     */
    public function sitemap()
    {
        return response()->view('sitemap')->header('Content-Type', 'text/xml');
    }

    /**
     * Logger
     * @param Request $request
     */
    public function logData(Request $request)
    {
        $type = $request->get('logger-type');

        if ($type == 'error') {
            logger()->error($request->all());
        } else if ($type == 'warning') {
            logger()->warning($request->all());
        } else {
            logger()->info($request->all());
        }
    }

    /**
     * Debugger javascript.js
     */
    public function debugbarJavascript()
    {
        return response()->file(resource_path('_debugbar/assets/javascript.js'));
    }

    /**
     * Debugger stylesheets.css
     */
    public function debugbarStylesheets()
    {
        return response()->file(resource_path('_debugbar/assets/stylesheets.css'));
    }
}
