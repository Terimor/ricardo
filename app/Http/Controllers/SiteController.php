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
    public static $emptyDomains = ['getsafemask.com', 'getsafemask.cc'];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $siteDisabled = \App::environment() === 'production' && time() > strtotime('2020-06-26 13:00') && !request()->get('fullcheckoutpage');
        if ($siteDisabled) {
            $domainName = Domain::getByName();
            \View::share('domainName', $domainName ? $domainName->getDisplayedName() : '');
            \View::share('siteDisabled', true);
        }
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
        if (!empty($domain->is_catch_all) || !empty($domain->product_type)) {
            $products = $productService->getAllBySoldOrTypeDomainsProducts($domain, (int)$request->get('p'), $request->get('search'));
            $isMultiproduct = true;
        } elseif (!empty($domain->is_multiproduct)) {
            $products = ProductService::getDomainProducts($domain);
            if ($products && count($products) > 0) {
                $isMultiproduct = true;
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
        return view('minishop/pages/home', compact('product', 'products', 'page_title', 'website_name', 'is_catch_all', 'cdn_url', 'pagination'));
    }

    /**
     * Redirects to 404 page if value in setting
     * @param string|null $value
     * @param string $setting_key
     */
    private function abortIfValueInSettingList(?string $value, string $setting_key): void
    {
        if ($value) {
            $list = Setting::getMultilineValueAsArray($setting_key);
            if (in_array($value, $list)) {
                abort(404);
            }
        }
    }

    /**
     * Redirects to 404 page if cop_id is ignored
     * @param string|null $cop_id
     */
    private function abortByCopId(?string $cop_id): void
    {
        $this->abortIfValueInSettingList($cop_id, 'blocked_cop_id');
    }

    /**
     * Redirects to 404 page if aff_id is ignored
     * @param string|null $aff_id
     */
    private function abortByAffId(?string $aff_id): void
    {
        $this->abortIfValueInSettingList($aff_id, 'blocked_aff_id');
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
        $placeholders['details'] = $placeholders['show_company_info'] ? t('privacy.address_details', ['address' => $placeholders['address'], 'phone' => $placeholders['phone'], 'number' => $placeholders['number']]) : '';
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
        $placeholders['details'] = $placeholders['show_company_info'] ? t('terms.company_details', ['address' => $placeholders['address']]) : '';
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
     * @param string $priceSet
     * @return mixed
     */
    public function checkout(Request $request, ProductService $productService, $priceSet = null)
    {
        if (!empty($priceSet)) {
            $request->merge(['cop_id' => $priceSet]);
        }
        // Abort request if cop_id in the list
        $this->abortByCopId($request->get('cop_id', $priceSet));

        // Abort request if aff_id in the list
        $this->abortByAffId($request->get('aff_id'));

        $redirect_view = $this->checkoutRequestView($request);
        if ($redirect_view) {
            return $redirect_view;
        }
        if ($request->get('3ds') === 'pending' && $request->get('3ds_restore') && $request->get('redirect_url')) {
            return redirect($request->get('redirect_url'));
        }

        if ($request->get('apm') && !$request->get('3ds')) {
            return redirect($request->fullUrl() . '&3ds=' . $request->get('apm'));
        }

        $loadedPhrases = (new I18nService())->loadPhrases('checkout_page');
        $product = $productService->resolveProduct($request, true);

        $is_checkout_page = $is_vrtl_page = $is_health_page = false;
        $new_engine_checkout_tpls = ['fmc5x', 'amc8', 'amc81'];
        if ($product->type == OdinOrder::TYPE_VIRTUAL) {
            $is_vrtl_page = true;
        } else {
            $is_checkout_page = true;
        }

        $is_checkout_new_engine_page = $is_checkout_page && in_array($request->get('tpl'), $new_engine_checkout_tpls);
        $is_health_page = Route::is('checkout_health') || Route::is('checkout_health_price_set');

        if ($is_checkout_page) {
            $viewTemplate = TemplateService::getCheckoutPageTemplate($request);
        }

        if ($is_health_page) {
            $viewTemplate = TemplateService::getHealthPageTemplate($request);
        }

        if ($is_vrtl_page) {
            $viewTemplate = TemplateService::getVirtualPageTemplate($request);
        }

        if ($request->get('3ds') === 'success' && $request->get('3ds_restore')) {
            return view('prerender.checkout.3ds_success', compact('product', 'is_vrtl_page'));
        }

        // load upsells only for vrlt templates
        //$upsells = $is_vrtl_page ? $productService->getProductUpsells($product) : [];
        $upsells = [];
        $setting = Setting::getValue(['ipqualityscore_api_hash', 'support_address', 'show_company_info']);

        $payment_api = PaymentApi::getActivePaypal();
        $setting['instant_payment_paypal_client_id'] = $payment_api->key ?? null;

        $countries =  \Utils::getShippingCountries(true, $product);

        $langCode = substr(app()->getLocale(), 0, 2);
        $countryCode = \Utils::getLocationCountryCode();

        // get available payment methods
        $setting['payment_methods'] = collect(
            PaymentService::getPaymentMethodsByCountry($countryCode, $request->get('cur'))
        )->collapse()->all();

        $recentlyBoughtData = OdinOrder::getRecentlyBoughtData();
        $recentlyBoughtNames = $recentlyBoughtData['recentlyBoughtNames'];
        $recentlyBoughtCities = $recentlyBoughtData['recentlyBoughtCities'];
        $product->addCityReviews($recentlyBoughtCities);

        $imagesNames = ['safe_payment'];
        $loadedImages = \Utils::getLocalizedImages($imagesNames);

        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('checkout.page_title'));
        $website_name = $domain->getWebsiteName($product, $request->get('cop_id'), $request->get('product'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));

        $company_address = TemplateService::getCompanyAddress($setting['support_address'], $domain, false, $setting['show_company_info']);
        $company_descriptor_prefix = '';

        $cdn_url = \Utils::getCdnUrl();

        $deals = [];
        $deals_main_quantities = [];
        $deals_free_quantities = [];
        $deal_promo = null;

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
     * Return view depends on request
     * @param Request $request
     * @return bool|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function checkoutRequestView(Request $request) {
        // hardcode
        $host = str_replace('www.', '', $request->getHost());
        if (in_array($host, static::$emptyDomains)) {
            return view('blank');
        }
        if ($request->get('emptypage') && $request->get('emptypage') == '1') {
            if (strlen($request->get('txid')) >= 20) {
                return view('prerender.checkout.txid_iframe');
            } else {
                return view('blank');
            }
        }
        if ($request->get('3ds') && !$request->get('3ds_restore')) {
            return view('prerender.checkout.3ds_restore');
        }
        return false;
    }

    /**
     * Upsells page
     * @param Request $request
     * @param ProductService $productService
     * @return type
     */
    public function upsells(Request $request, ProductService $productService)
    {
        $cdn_url = \Utils::getCdnUrl();
        $loadedPhrases = (new I18nService())->loadPhrases('upsells_page');
		$product = $productService->resolveProduct($request, true);

		$viewTemplate = $product->type == OdinOrder::TYPE_VIRTUAL ? 'new.pages.vrtl.upsells' : 'uppsells_funnel';

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
        // if we have order parameter check virtual order to redirect to another view
        $orderCustomer = $request->get('order') ? OrderService::getCustomerDataByOrderId($request->get('order'), true) : null;
        if (!$orderCustomer) {
            // generate global get parameters
            $params = \Utils::getGlobalGetParameters($request);
            return redirect('/checkout'.$params);
        }
        if ($orderCustomer && $orderCustomer->type == OdinOrder::TYPE_VIRTUAL) {
            return $this->getVirtualOrderView($orderCustomer, $productService, $request);
        }

        $loadedPhrases = (new I18nService())->loadPhrases('thankyou_page');
		$product = $productService->resolveProduct($request, true);

        $viewTemplate = 'thankyou';

        $payment_api = PaymentApi::getActivePaypal();
        $setting['instant_payment_paypal_client_id'] = $payment_api->key ?? null;

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

        $freeFile = null;
        if (!empty($product->free_file_id)) {
            // TODO: Enable EBOOK
            //$freeFile = ProductService::getLocaleFreeFileByFileId($product->free_file_id);
        }

        return view($viewTemplate, compact(
            'countryCode', 'payment_method', 'product' , 'setting', 'orderCustomer', 'loadedPhrases', 'order_aff', 'page_title', 'main_logo',
            'is_thankyou', 'is_thankyou_page', 'is_vrtl_thankyou_page', 'is_smartbell', 'freeFile'
        ));
    }

    /**
     * Action for virtual order download page
     * @param string $orderId
     * @param string $orderNumber
     * @param ProductService $productService
     * @throws \App\Exceptions\OrderNotFoundException
     * @throws \App\Exceptions\ProductNotFoundException
     * @return \Illuminate\View\View
     */
    public function virtualOrderDownload(string $orderId, string $orderNumber, ProductService $productService, Request $request): \Illuminate\View\View {
        // after add here, still add to OrderService::getCustomerDataByOrderId();
        $select = ['number', 'type', 'products', 'customer_email', 'customer_first_name', 'customer_last_name', 'txns.status', 'total_paid_usd', 'total_refunded_usd'];
        $order = OdinOrder::getByIdAndNumber($orderId, $orderNumber, $select, false);
        return $this->getVirtualOrderView($order, $productService, $request);
    }

    /**
     * @param OdinOrder|null $order
     * @param ProductService $productService
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \App\Exceptions\ProductNotFoundException
     */
    private function getVirtualOrderView(?OdinOrder $order, ProductService $productService, Request $request) {
        if (!$order) {
            abort(404, 'Sorry, we couldn\'t find your order');
        }
        if (!$order->hasMediaAccess()) {
            abort(403);
        }
        $sku = $order->getMainSku();
        // add select fields for page
        $select = ['type', 'product_name', 'description.en', 'free_file_ids', 'sale_file_ids', 'sale_video_ids', 'logo_image_id',
            'billing_description', 'image_ids', 'bg_image_id'];
        $select = app()->getLocale() != 'en' ? \Utils::addLangFieldToSelect($select, app()->getLocale()) : $select;

        $product = OdinProduct::getBySku($sku, false, $select);
        if (!$product) {
            abort(404, 'Sorry, we couldn\'t find your order');
        }
        $loadedPhrases = (new I18nService())->loadPhrases('thankyou_page');
        // prepare product
        $product->setLocalImages();
        $upsells = OdinProduct::getBySkus($order->getUpsellsSkus(), ['sale_file_ids', 'sale_video_ids']);
        $product = $productService->getLocaleDownloadProduct($product, $order->number, $upsells);

        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product);
        $support_email = Setting::getValue('support_email');

        return view('new.pages.vrtl.thankyou', compact('product', 'page_title', 'loadedPhrases', 'support_email'));
    }

    /**
     * Splash page
     * @return type
     */
    public function splash(Request $request, ProductService $productService)
    {
        // hardcode
        $host = str_replace('www.', '', $request->getHost());
        if (in_array($host, static::$emptyDomains)) {
            return view('blank');
        }
        $loadedPhrases = (new I18nService())->loadPhrases('splash_page');
        $product = $productService->resolveProduct($request, true);
        $cdn_url = \Utils::getCdnUrl();
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), '');
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));

        if ($product->type === OdinProduct::TYPE_PHYSICAL) {
            return view('splash', compact('loadedPhrases', 'product', 'cdn_url', 'page_title', 'main_logo'));
        } else {
            return view('new.pages.vrtl.splash', compact('loadedPhrases', 'product', 'cdn_url', 'page_title', 'main_logo'));
        }
    }

    /**
     * Report abuse page /report-abuse
     * @param Request $request
     * @param ProductService $productService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reportAbuse(Request $request, ProductService $productService)
    {
        $loadedPhrases = (new I18nService())->loadPhrases('abuse_page');
        $product = $productService->resolveProduct($request, true);
        $domain = Domain::getByName();
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('abuse.title'));
        $main_logo = $domain->getMainLogo($product, $request->get('cop_id'));
        $website_name = $domain->getWebsiteName($product, $request->get('cop_id'), $request->get('product'));
        $placeholders = TemplateService::getCompanyData($domain);
        return view('report_abuse', compact('loadedPhrases', 'product', 'page_title', 'main_logo', 'website_name', 'placeholders'));
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

        // Disable prober payment stats
        /*
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
                    logger()->error("Prober: lower limit exceeded", ['prv' => $prv, 'pct' => $pct, 'limit' => $txn_limits[$prv]]);
                }
            } else {
                $result = $bad;
                $prv_res['status'] = 0;
                logger()->error("Prober: the setting 'prober_txns_success_limits' must have the provider [{$prv}]");
            }
            $txn_result[] = $prv_res;
        }
        */

        // return view('prober', compact('result', 'redis', 'success_orders', 'firing', 'setting', 'txn_result'));
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

    /**
     * Support page
     * @param Request $request
     * @param ProductService $productService
     * @param I18nService $i18nService
     * @param string|null $code
     * @param string|null $email
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function support(Request $request, ProductService $productService, I18nService $i18nService, ?string $code = null, ?string $email = null)
    {
        $domain = Domain::getByName();
        $loadedPhrases = $i18nService->loadPhrases('support_page');
        $product = $productService->resolveProduct($request, true);
        $page_title = \Utils::generatePageTitle($domain, $product, $request->get('cop_id'), t('support.title'));
        $countries =  \Utils::getShippingCountries(true, $product);
        return view('support', compact('domain', 'product', 'page_title', 'loadedPhrases', 'code', 'email', 'countries'));
    }


    /**
     * Generate code for accessing order and sending to customer email
     * @param Request $request
     * @param \App\Services\EmailService $emailService
     * @param OrderService $orderService
     * @param I18nService $i18nService
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestSupportCode(Request $request, \App\Services\EmailService $emailService, OrderService $orderService, I18nService $i18nService)
    {
        $domain = Domain::getByName();
        $email = mb_strtolower(trim($request->get('email')));
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $orders = OdinOrder::getByEmail($email, ['status']);
        if ($orders->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => $i18nService->getPhraseTranslation('support.order.not_found')
            ]);
        }

        $code = $orderService->generateSupportCode($email);
        $result = $emailService->sendSupportCodeToCustomer($code, $email, $domain->name);

        if (!empty($result['status'])) {
            return response()->json([
                'status' => 1,
                'message' => $i18nService->getPhraseTranslation('support.code.sent')
            ]);
        }

        logger()->error("request support code did not work, {$code} - {$email} - {$domain->name}");
        return response()->json([
            'status' => 500,
            'message' => 'Something went wrong!'
        ]);

    }

    /**
     * Validating support code and email and return orders info for support page
     * @param Request $request
     * @param OrderService $orderService
     * @param I18nService $i18nService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderInfo(Request $request, OrderService $orderService, I18nService $i18nService)
    {
        $i18nService->loadPhrases('support_page');
        $email = mb_strtolower(trim($request->get('email')));
        $code = trim($request->get('code'));
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6']
        ]);

        $orders = $orderService->getOrdersByEmailAndSupportCode($email, $code);
        if (!$orders) {
            return response()->json([
                'status' => 404,
                'message' => t('support.code_is_invalid')
            ]);
        }

        return response()->json([
            'status' => 1,
            'orders' => $orders
        ]);
    }

    /**
     * Handle request of changing order address in support page
     * @param \App\Http\Requests\ChangeOrderAddressRequest $request
     * @param OrderService $orderService
     * @param I18nService $i18nService
     * @param \App\services\EmailService $emailService
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\OrderNotFoundException
     */
    public function changeOrderAddress(\App\Http\Requests\ChangeOrderAddressRequest $request, OrderService $orderService, I18nService $i18nService, \App\services\EmailService $emailService)
    {
        $email = $request->get('email');
        $code = $request->get('code');
        $orderNumber = $request->get('number');

        if (!$orderService->validateSupportCode($email, $code)) {
            return response()->json([
                'status' => 0,
                'message' => $i18nService->getPhraseTranslation('support.code_is_invalid')
            ]);
        }
        $order = OdinOrder::getByNumber($orderNumber);
        if ($order->customer_email !== $email) {

            logger()->error("trying access to order {$order->number} with different email {$order->customer_email} - {$email}");
            return response()->json([
                'status' => 0,
                'message' => 'Invalid email'
            ]);
        }

        $mappingFields = $orderService->getShippingFieldsMapping();
        $shippingData = [];
        foreach ($mappingFields as $field => $name) {
            $shippingData[$field] = $request->get($name);
        }

        $orderData = $orderService->updateShippingAddress($order, $shippingData);
        if (!$orderData) {
            logger()->error("Update shipping address error, Order {$order->number} ".json_encode($shippingData));
            return response()->json([
                'status' => 0,
                'message' => 'Something went wrong!'
            ]);
        }
        $domain = Domain::getByName();
        $emailResult = $emailService->notifyCustomerAddressChange($order->number, $domain->name);
        if (empty($emailResult['status'])) {
            logger()->error("Address change email sending failed, Order {$order->number},  ".json_encode($emailResult));
        }
        return response()->json([
            'status' => 1,
            'message' => $i18nService->getPhraseTranslation('support.address.changed'),
            'order' => $orderData
        ]);
    }

}
