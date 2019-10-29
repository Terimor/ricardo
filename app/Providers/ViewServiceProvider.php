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
            $view->with('HasVueApp', Request::is('checkout') || Route::is('upsells') || Route::is('thankyou') || Route::is('order-tracking'));
            $view->with('PayPalCurrency', UtilsService::getPayPalCurrencyCode());
            $view->with('SentryDsn', Setting::getValue('sentry_dsn'));
            $view->with('ga_id', optional(Domain::getByName())->ga_id);
            
            $affiliate = null;
            if (Request::get('aff_id')) {
                $affiliate = AffiliateSetting::getByHasOfferId(Request::get('aff_id'));
            }
            $view->with('htmlToApp', AffiliateService::getHtmlToApp(Request(), $affiliate));

            $lang = substr(app()->getLocale(), 0, 2);
            $view->with('direction', request()->is('checkout') && in_array($lang, ['he', 'ar']) ? 'rtl' : 'ltr');
        });

        View::composer('layouts.footer', function($view) {
            $affiliate = null;
            if (Request::get('aff_id')) {
                $affiliate = AffiliateSetting::getByHasOfferId(Request::get('aff_id'));
            }
            $view->with('aff', AffiliateSetting::getLocaleAffiliate($affiliate));
        });

        View::composer('thankyou', function($view) {
            $view->with('cdnUrl', UtilsService::getCdnUrl());
        });
    }
}
