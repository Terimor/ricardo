<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\Setting;
use App\Models\AffiliateSetting;
use App\Services\UtilsService;
use App\Services\AffiliateService;
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
            $view->with('cdnUrl', UtilsService::getCdnUrl());
            $view->with('HasVueApp', Request::is('checkout') || Route::is('upsells') || Route::is('thankyou') || Route::is('order-tracking') || Route::is('checkout_price_set'));
            $view->with('PayPalCurrency', UtilsService::getPayPalCurrencyCode());
            $view->with('SentryDsn', Setting::getValue('sentry_dsn'));
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
    }
}
