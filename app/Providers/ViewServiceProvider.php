<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\Setting;
use App\Models\AffiliateSetting;
use App\Models\I18n;
use App\Services\UtilsService;
use App\Services\AffiliateService;
use App\Services\I18nService;
use App\Services\MiniShopService;
use App\Http\Controllers\MiniShopController;
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
            $settings = Setting::getValue(array(
                'sentry_dsn',
                'freshchat_token'
            ));

            $view->with('cdnUrl', UtilsService::getCdnUrl());
            $view->with('HasVueApp', Request::is('checkout') || Route::is('upsells') || Route::is('thankyou') || Route::is('order-tracking') || Route::is('checkout_price_set'));
            $view->with('PayPalCurrency', UtilsService::getPayPalCurrencyCode());
            $view->with('SentryDsn', $settings['sentry_dsn']);
            $view->with('FreshchatToken', $settings['freshchat_token']);
            $view->with('ga_id', optional(Domain::getByName())->ga_id);
            
            $affiliate = null;
            $affId = AffiliateService::getAffIdFromRequest(Request());            
            if ($affId) {                
                $affiliate = AffiliateSetting::getByHasOfferId($affId);
            }
            $view->with('htmlToApp', AffiliateService::getHtmlToApp(Request(), $affiliate));

            $lang = substr(app()->getLocale(), 0, 2);
            $view->with('direction', in_array($lang, ['he', 'ar']) ? 'rtl' : 'ltr');
        });

        View::composer('layouts.footer', function($view) {
            $affiliate = null;
            $affId = AffiliateService::getAffIdFromRequest(Request());            
            if ($affId) {                
                $affiliate = AffiliateSetting::getByHasOfferId($affId);
            }
            $view->with('aff', AffiliateSetting::getLocaleAffiliate($affiliate));
            $view->with('is_aff_id_empty', AffiliateService::isAffiliateRequestEmpty(Request()));
        });

        View::composer('thankyou', function($view) {
            $view->with('cdnUrl', UtilsService::getCdnUrl());
        });

        // run minishop boot
        if (Request::is('/')) {        
            static::miniShopBoot();
        }
    }
    
    /**
     * Mini shop boot
     */
    public static function miniShopBoot()
    {
        (new I18nService())->loadPhrases('minishop_page');
        $settings = Setting::getValue(['sentry_dsn', 'freshchat_token']);

        $lang = app()->getLocale();
        $domain = Domain::getByName();
        $cdn_url = UtilsService::getCdnUrl();

        $direction = !in_array($lang, ['he', 'ar'])
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

        // Layout
        View::composer('minishop.layout', function($view) use ($lang, $direction) {
            $view->with('lang_locale', $lang);
            $view->with('lang_direction', $direction);
        });

        // Meta Tags
        View::composer('minishop.layout.meta', function($view) use ($domain) {
            $view->with('ga_id', optional($domain)->ga_id);
        });

        // JS Deps
        View::composer('minishop.layout.js_deps', function($view) {
            $view->with('show_deps', [
                'lato.css',
                'awesome.css',
                'bootstrap.css',
            ]);
        });

        // Google Tag Manager
        View::composer('minishop.scripts.gtags', function($view) use ($html_to_app) {
            $view->with('html_to_app', $html_to_app);
        });

        // Google Analytics
        View::composer('minishop.scripts.analytics', function($view) use ($domain) {
            $view->with('ga_id', optional($domain)->ga_id);
        });

        // Sentry.io
        View::composer('minishop.scripts.sentry', function($view) use ($settings) {
            $view->with('sentry_dsn', $settings['sentry_dsn']);
        });

        // Freshchat
        View::composer('minishop.scripts.freshchat', function($view) use ($settings) {
            $view->with('freshchat_token', $settings['freshchat_token']);
        });

        // Header Logo
        View::composer('minishop.regions.header.logo', function($view) use ($domain, $cdn_url) {
            $view->with('domain_logo', optional($domain)->logo ?? $cdn_url . MiniShopService::$headerLogoDefaultPath);
        });

        // Header Menu
        View::composer('minishop.regions.header.menu', function($view) {
            $view->with('header_menu', MiniShopService::$headerMenu);
        });

        // Footer
        View::composer('minishop.regions.footer', function($view) use ($is_aff_id_empty) {
            $view->with('is_aff_id_empty', $is_aff_id_empty);
        });

        // Footer Menu
        View::composer('minishop.regions.footer.menu', function($view) use ($is_signup_hidden) {
            $view->with('is_signup_hidden', $is_signup_hidden);
        });

        // Freshchat (Custom Image)
        View::composer('minishop.regions.fixed.freshchat', function($view) use ($settings) {
            $view->with('freshchat_token', $settings['freshchat_token']);
        });

        // Google Tag Manager (No Script)
        View::composer('minishop.scripts.gtags_ns', function($view) use ($html_to_app) {
            $view->with('html_to_app', $html_to_app);
        });

        // Pixels
        View::composer('minishop.scripts.pixels', function($view) use ($html_to_app) {
            $view->with('html_to_app', $html_to_app);
        });
    }
}
