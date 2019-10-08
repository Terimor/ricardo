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
        $affiliate = null;
        if (Request::get('affid')) {
            $affiliate = AffiliateSetting::getByHasOfferId(Request::get('affid'));
        }
        
        View::composer('layouts.app', function($view) use ($affiliate) {            
            $view->with('HasVueApp', Request::is('checkout') || Route::is('upsells') || Route::is('thankyou') || Route::is('order-tracking'));
            $view->with('PayPalCurrency', UtilsService::getPayPalCurrencyCode());
            $view->with('SentryDsn', Setting::getValue('sentry_dsn'));
            $view->with('ga_id', optional(Domain::getByName())->ga_id);
            $view->with('pixels', AffiliateService::getPixels(Request(), $affiliate));
        });

        View::composer('layouts.footer', function($view) use ($affiliate) {
            $view->with('aff', AffiliateSetting::getLocaleAffiliate($affiliate));
        });
    }
}
