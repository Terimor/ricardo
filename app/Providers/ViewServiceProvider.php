<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\Setting;
use App\Models\AffiliateSetting;
use App\Services\UtilsService;
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
            $view->with('HasVueApp', Request::is('checkout') || Route::is('upsells') || Route::is('thankyou'));
            $view->with('PayPalCurrency', UtilsService::getPayPalCurrencyCode());
            $view->with('SentryDsn', Setting::getValue('sentry_dsn'));
            $view->with('ga_id', (optional(Domain::getByName()))->ga_id);
        });
        
        View::composer('layouts.footer', function($view) {
            $view->with('aff', AffiliateSetting::getLocaleAffiliate(Request::get('affid')));
        });
    }
}
