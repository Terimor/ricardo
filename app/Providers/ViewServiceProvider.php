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
        });

        View::composer('thankyou', function($view) {
            $view->with('cdnUrl', UtilsService::getCdnUrl());
        });

        // run minishop boot
        static::miniShopBoot();
    }
    
    /**
     * Mini shop boot
     */
    public static function miniShopBoot()
    {
        View::composer('minishop.*', function($view) {
            $req = Request();

            $lang = app()->getLocale();
            $cdn_url = UtilsService::getCdnUrl();

            $domain = Domain::getByName();
            optional($domain)->setLocalLogo();

            $aff_id = AffiliateService::getAffIdFromRequest($req);

            if ($aff_id) {                
              $affiliate = AffiliateSetting::getByHasOfferId($aff_id);
            }

            $htmlToApp = AffiliateService::getHtmlToApp($req, $affiliate ?? null);
            $i18n = (new I18nService())->loadPhrases('minishop_page');

            $settings = Setting::getValue(['sentry_dsn']);

            $view->with('htmlToApp', $htmlToApp);

            // All
            $view->with('i18n', $i18n);
            $view->with('domain', $domain);
            $view->with('cdn_url', $cdn_url);

            // Layout
            View::composer('minishop.layout', function($view) use ($lang) {
              $direction = !in_array($lang, ['he', 'ar'])
                ? 'ltr'
                : 'rtl';

              $view->with('lang_locale', $lang);
              $view->with('lang_direction', $direction);
            });

            // JS Deps
            View::composer('minishop.layout.js_deps', function($view) {
              $view->with('show_deps', [
                'lato.css',
                'awesome.css',
                'bootstrap.css',
              ]);
            });

            // Google Analytics
            View::composer('minishop.scripts.sentry', function($view) use ($domain) {
              $view->with('ga_id', optional($domain)->ga_id);
            });

            // Sentry.io
            View::composer('minishop.scripts.sentry', function($view) use ($settings) {
              $view->with('sentry_dsn', $settings['sentry_dsn']);
            });

            // Header Logo
            View::composer('minishop.regions.header.logo', function($view) use ($cdn_url, $domain) {
              $view->with('domain_logo', optional($domain)->logo ?? $cdn_url . MiniShopService::$headerLogoDefaultPath);
            });
            // Header Menu
            View::composer('minishop.regions.header.menu', function($view) {
              $view->with('header_menu', MiniShopService::$headerMenu);
            });

            // Footer Menu
            View::composer('minishop.regions.footer.menu', function($view) {
              $view->with('footer_menu', MiniShopService::$footerMenu);
            });
        });
    }
}
