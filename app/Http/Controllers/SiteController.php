<?php

namespace App\Http\Controllers;

use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\OdinProduct;
use App\Models\Setting;
use App\Models\PaymentApi;
use App\Models\OdinOrder;
use App\Models\Domain;
use App\Services\CustomerService;
use App\Services\ProductService;
use App\Services\PaymentService;
use App\Services\AffiliateService;
use App\Services\I18nService;
use App\Services\OrderService;
use App\Services\ViacepService;
use App\Services\TemplateService;
use App\Services\UtilsService;
use App\Constants\PaymentMethods;
use App\Constants\PaymentProviders;
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
                $products = $productService->getAllSoldDomainsProducts($domain, (int)$request->get('p'), $request->get('search'));
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
     * Show Support page
     * @param  Request  $request
     * @param  ProductService  $productService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function support(Request $request,ProductService $productService) {
        $data = [
            'product' => $productService->resolveProduct($request, true),
            'page_title' => 'Support',
            'info' => $info ?? null,
        ];
        return view('support', $data);
    }

    /**
     * Show Support page with search results
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function supportRequest(Request $request) {

        if (isset($request['search']) && $request['search']) {
            $search = trim($request['search']);
            $odinOrders =  OdinOrder::getByEmailOrTrackingNumber($search,['number', 'trackings.number', 'trackings.aftership_slug', 'products.sku_code', 'products.quantity']);
            $info = [];
            if ($odinOrders->isNotEmpty()) {
                foreach ($odinOrders as $order) {
                    foreach ($order->trackings ?? [] as $tracking) {
                        $products = array_map(function ($product) {
                            return $product['quantity'].' × '.OdinProduct::getBySku($product['sku_code'])->product_name;
                        }, $order->products);
                        $info[] = [
                            'order_number' => $order->number,
                            'products' => implode('<br>', $products),
                            'link' => UtilsService::generateTrackingLink($tracking['number'], $tracking['aftership_slug']),
                        ];
                    }
                }
                if (empty($info)) {
                    $info = 'Your package is in the processing facility. we will send you the tracking number soon';
                }
            } else {
                $info = 'Email not found';
            }
        } else {
            $info = 'The search is required';
        }

        return response()->view('components.support.order_info', compact('info'));
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
        $new_engine_checkout_tpls = ['fmc5x', 'amc8', 'amc81'];
        $is_checkout_page = Route::is('checkout') || Route::is('checkout_price_set');
        $is_checkout_new_engine_page = $is_checkout_page && in_array($request->get('tpl'), $new_engine_checkout_tpls);
        $is_health_page = Route::is('checkout_health') || Route::is('checkout_health_price_set');
        $is_vrtl_page = Route::is('checkout_vrtl') || Route::is('checkout_vrtl_price_set');

        if ($is_checkout_page) {
            $viewTemplate = 'checkout';

            if ($request->get('tpl') == 'vmp41') {
                $viewTemplate = 'vmp41';
            }
            if ($request->get('tpl') == 'vmp42') {
                $viewTemplate = 'vmp42';
            }
            if ($request->get('tpl') == 'fmc5x') {
                $viewTemplate = 'new.pages.checkout.templates.fmc5';
            }
            if ($request->get('tpl') == 'amc8') {
                $viewTemplate = 'new.pages.checkout.templates.amc8';
            }
            if ($request->get('tpl') == 'amc81') {
                $viewTemplate = 'new.pages.checkout.templates.amc81';
            }
        }

        if ($is_health_page) {
            $viewTemplate = 'new.pages.checkout.templates.hp01';

            if ($request->get('tpl') == 'thor-power') {
                $viewTemplate = 'new.pages.checkout.templates.thor-power';
            }
            if ($request->get('tpl') == 'hydrolinx') {
                $viewTemplate = 'new.pages.checkout.templates.hydrolinx';
            }
            if ($request->get('tpl') == 'slimeazy') {
                $viewTemplate = 'new.pages.checkout.templates.slimeazy';
            }
        }

        if ($is_vrtl_page) {
            $viewTemplate = 'new.pages.checkout.templates.vc1';

            if ($request->get('tpl') == 'vc1') {
                $viewTemplate = 'new.pages.checkout.templates.vc1';
            }
            if ($request->get('tpl') == 'vc2') {
                $viewTemplate = 'new.pages.checkout.templates.vc2';
            }
        }

        if (!empty($priceSet)) {
            $request->merge(['cop_id' => $priceSet]);
        }

        $product = $productService->resolveProduct($request, true);
        // load upsells only for vrlt templates
        $upsells = [];
        if ($is_vrtl_page) {
            $upsells = $productService->getProductUpsells($product);
        }

        if ($request->get('emptypage') && strlen($request->get('txid')) >= 20) {
            return view('prerender.checkout.txid_iframe');
        }

        if ($request->get('apm') && !$request->get('3ds')) {
            return redirect($request->fullUrl() . '&3ds=' . $request->get('apm'));
        }

        if ($request->get('3ds') && !$request->get('3ds_restore')) {
            return view('prerender.checkout.3ds_restore');
        }

        if ($request->get('3ds') === 'success' && $request->get('3ds_restore')) {
            return view('prerender.checkout.3ds_success', compact('product', 'is_vrtl_page'));
        }

        if ($request->get('3ds') === 'pending' && $request->get('3ds_restore') && $request->get('redirect_url')) {
            return redirect($request->get('redirect_url'));
        }

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

        $is_checkout = $is_checkout_page || $is_health_page || $is_vrtl_page;
        $is_new_engine = $is_checkout_new_engine_page || $is_health_page || $is_vrtl_page;
        $is_smartbell = str_replace('www.', '', $request->getHost()) === 'smartbell.pro';

        if ($is_new_engine) {
            $data_deals = TemplateService::getDealsData($product, $request);
            $deals = $data_deals['deals'];
            $deal_promo = $data_deals['deal_promo'];
            $deals_main_quantities = $data_deals['deals_main_quantities'];
            $deals_free_quantities = $data_deals['deals_free_quantities'];
        }

        // get customer by number for autofill
        $customer = null;
        if ($request->get('customer')) {
            $customer = CustomerService::getLocaleCustomerByNumber($request->get('customer'));
        }

        return view(
            $viewTemplate,
            compact(
                'langCode', 'countryCode', 'product', 'setting', 'countries', 'loadedPhrases',
                'recentlyBoughtNames', 'recentlyBoughtCities', 'loadedImages', 'priceSet', 'page_title', 'main_logo',
                'company_address', 'company_descriptor_prefix', 'cdn_url', 'upsells', 'website_name',
                'deals', 'deal_promo', 'deals_main_quantities', 'deals_free_quantities', 'customer',
                'is_checkout', 'is_new_engine', 'is_checkout_page', 'is_health_page', 'is_vrtl_page', 'is_smartbell'
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
        $viewTemplate = 'uppsells_funnel';

        if (Route::is('upsells_vrtl')) {
            $viewTemplate = 'new.pages.vrtl.upsells';
        }

        $cdn_url = \Utils::getCdnUrl();
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

        $is_upsells_page = Route::is('upsells');
        $is_vrtl_upsells_page = Route::is('upsells_vrtl');
        $is_upsells = $is_upsells_page || $is_vrtl_upsells_page;
        $is_smartbell = str_replace('www.', '', $request->getHost()) === 'smartbell.pro';
        $is_new_engine = $is_vrtl_upsells_page;

        return view($viewTemplate, compact(
            'cdn_url', 'countryCode', 'product', 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff', 'page_title', 'main_logo',
            'is_upsells', 'is_new_engine', 'is_upsells_page', 'is_vrtl_upsells_page', 'is_smartbell'
        ));
    }

    /**
     * Thankyou page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function thankyou(Request $request, ProductService $productService)
    {
        $viewTemplate = 'thankyou';

        if (Route::is('thankyou_vrtl')) {
            $viewTemplate = 'new.pages.vrtl.thankyou';
        }

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

        $is_thankyou_page = Route::is('thankyou');
        $is_vrtl_thankyou_page = Route::is('thankyou_vrtl');
        $is_thankyou = $is_thankyou_page || $is_vrtl_thankyou_page;
        $is_smartbell = str_replace('www.', '', $request->getHost()) === 'smartbell.pro';

        if ($request->get('3ds') && !$request->get('3ds_restore')) {
            return view('prerender.thankyou.3ds_restore');
        }

        if ($request->get('apm') && !$request->get('apm_restore')) {
            return view('prerender.thankyou.apm_restore');
        }

        return view($viewTemplate, compact(
            'countryCode', 'payment_method', 'product' , 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff', 'page_title', 'main_logo',
            'is_thankyou', 'is_thankyou_page', 'is_vrtl_thankyou_page', 'is_smartbell'
        ));
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
            'prober_orders_count',
            'prober_orders_success_min',
            'prober_txn_success_limits'
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

        $success_orders = OrderService::getLastOrdersTxnSuccessPercent(
            (int)$setting['prober_orders_count'],
            (float)$setting['prober_orders_success_min']
        );

        if ($success_orders <= (float)$setting['prober_orders_success_min']) {
            $result = $bad;
        }

        $firing = OrderService::getLastOrdersFiringPercent((int)$setting['prober_firing_orders'], (float)$setting['prober_firing_percent']);

        if ($firing <= (float)$setting['prober_firing_percent']) {
            $result = $bad;
        }

        $txn_report = OrderService::getRecentSuccessTxnReportInPct($setting['prober_orders_count']);

        $txn_result = [];
        $txn_limits = json_decode($setting['prober_txn_success_limits'], true) ?? [];
        foreach($txn_report as $prv => $pct) {
            $prv_res = ['name' => PaymentProviders::$list[$prv]['name'], 'percent' => $pct, 'status' => 1];
            if (isset($txn_limits[$prv])) {
                if ($pct < $txn_limits[$prv]) {
                    $result = $bad;
                    $prv_res['status'] = 0;
                }
            } else {
                $result = $bad;
                $prv_res['status'] = 0;
                logger()->warning("Prober: the setting 'prober_txns_success_limits' must have the provider [{$prv}]");
            }
            $txn_result[] = $prv_res;
        }

        return view('prober', compact('result', 'redis', 'success_orders', 'firing', 'setting', 'txn_result'));
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

    /**
     * Temporary hook
     */
    public function newCustomer()
    {
        return response(null, 200);
    }
}
