<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\Setting;
use App\Models\AffiliateSetting;
use App\Services\UtilsService;
use App\Services\AffiliateService;
use App\Services\MiniShopService;
use App\Http\Controllers\MiniShopController;
use App\Services\TemplateService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.app', function($view) {
            $settings = Setting::getValue([
                'sentry_dsn',
                'freshchat_token'
            ]);

            $new_engine_checkout_tpls = ['fmc5x', 'amc8', 'amc81'];
            $is_checkout_page = Route::is('checkout') || Route::is('checkout_price_set');
            $is_checkout_new_engine_page = $is_checkout_page && in_array(Request::get('tpl'), $new_engine_checkout_tpls);
            $is_health_page = Route::is('checkout_health') || Route::is('checkout_health_price_set');
            $is_vrtl_page = Route::is('checkout_vrtl') || Route::is('checkout_vrtl_price_set');
            $is_upsells_page = Route::is('upsells');
            $is_vrtl_upsells_page = Route::is('upsells_vrtl');
            $is_checkout = $is_checkout_page || $is_health_page || $is_vrtl_page;
            $is_upsells = $is_upsells_page || $is_vrtl_upsells_page;

            $is_thankyou_page = Route::is('thankyou');
            $is_vrtl_thankyou_page = Route::is('thankyou_vrtl');
            $is_thankyou = $is_thankyou_page || $is_vrtl_thankyou_page;

            $view->with('cdn_url', UtilsService::getCdnUrl());
            $view->with('HasVueApp', $is_checkout || $is_upsells || $is_thankyou || Route::is('order-tracking'));
            $view->with('PayPalCurrency', UtilsService::getPayPalCurrencyCode());
            $view->with('sentry_dsn', $settings['sentry_dsn']);
            $view->with('FreshchatToken', $settings['freshchat_token']);
            $view->with('ga_id', optional(Domain::getByName())->ga_id);

            $affiliate = null;
            $affId = AffiliateService::getAffIdFromRequest(Request());
            if ($affId) {
                $affiliate = AffiliateSetting::getByHasOfferId($affId);
            }
            $view->with('html_to_app', AffiliateService::getHtmlToApp(Request(), $affiliate));

            $view->with('lang_locale', app()->getLocale());
            $view->with('lang_direction', in_array(app()->getLocale(), ['ar', 'he', 'ur']) ? 'rtl' : 'ltr');
            $view->with('is_new_engine', $is_checkout_new_engine_page || $is_health_page || $is_vrtl_page || $is_vrtl_upsells_page);
        });

        View::composer(['layouts.footer', 'new.regions.footer'], function($view) {
            $affiliate = null;
            $affId = AffiliateService::getAffIdFromRequest(Request());
            if ($affId) {
                $affiliate = AffiliateSetting::getByHasOfferId($affId);
            }
            $domain = Domain::getByName();
            $view->with('aff', AffiliateSetting::getLocaleAffiliate($affiliate));
            $view->with('is_aff_id_empty', AffiliateService::isAffiliateRequestEmpty(Request()));
            $setting = Setting::getValue([
                'support_address',
                'privacy_off'
            ]);
            $view->with('company_address', TemplateService::getCompanyAddress($setting['support_address'], $domain, false, $setting['privacy_off'] ?? 0));
        });

        View::composer('thankyou', function($view) {
            $view->with('cdn_url', UtilsService::getCdnUrl());
        });

        // run minishop boot
        if (Request::is('/') || Request::is('product')) {
            static::miniShopBoot();
        }
    }

    /**
     * Mini shop boot
     */
    public static function miniShopBoot()
    {
        // Layout
        View::composer('minishop.layout', function($view) {
            $settings = Setting::getValue(['sentry_dsn', 'freshchat_token', 'support_address', 'privacy_off']);

            $lang = app()->getLocale();
            $domain = Domain::getByName();
            $cdn_url = UtilsService::getCdnUrl();

            $direction = !in_array($lang, ['ar', 'he', 'ur'])
                ? 'ltr'
                : 'rtl';

            $req = Request();
            $aff_id = AffiliateService::getAffIdFromRequest($req);

            if ($aff_id) {
              $affiliate = AffiliateSetting::getByHasOfferId($aff_id);
            }

            $html_to_app = AffiliateService::getHtmlToApp($req, $affiliate ?? null);
            $is_aff_id_empty = AffiliateService::isAffiliateRequestEmpty($req);

            $locale_affiliate = AffiliateSetting::getLocaleAffiliate($affiliate ?? null);
            $is_signup_hidden = $locale_affiliate['is_signup_hidden'] ?? false;

            $new_engine_checkout_tpls = ['fmc5x', 'amc8', 'amc81'];
            $is_checkout_page = Route::is('checkout') || Route::is('checkout_price_set');
            $is_checkout_new_engine_page = $is_checkout_page && in_array(Request::get('tpl'), $new_engine_checkout_tpls);
            $is_health_page = Route::is('checkout_health') || Route::is('checkout_health_price_set');
            $is_vrtl_page = Route::is('checkout_vrtl') || Route::is('checkout_vrtl_price_set');
            $is_vrtl_upsells_page = Route::is('upsells_vrtl');
            $is_vrtl_thankyou_page = Route::is('thankyou_vrtl');

            $view->with('is_minishop', true);
            $view->with('lang_locale', $lang);
            $view->with('lang_direction', $direction);
            $view->with('ga_id', optional($domain)->ga_id);
            $view->with('html_to_app', $html_to_app);
            $view->with('cdn_url', $cdn_url);
            $view->with('sentry_dsn', $settings['sentry_dsn']);
            $view->with('freshchat_token', $settings['freshchat_token']);
            $view->with('domain_logo', optional($domain)->logo ?? $cdn_url . MiniShopService::$headerLogoDefaultPath);
            $view->with('header_menu', MiniShopService::$headerMenu);
            $view->with('is_aff_id_empty', $is_aff_id_empty);
            $view->with('is_signup_hidden', $is_signup_hidden);
            $view->with('company_address', TemplateService::getCompanyAddress($settings['support_address'], $domain, false, $settings['privacy_off'] ?? 0));
            $view->with('is_new_engine', $is_checkout_new_engine_page || $is_health_page || $is_vrtl_page || $is_vrtl_upsells_page || $is_vrtl_thankyou_page);
        });
    }
}
