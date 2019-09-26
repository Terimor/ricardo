<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\Setting;
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
            $view->with('ga_id', Domain::where('name', request()->getHost())->pluck('ga_id')->first());
        });
    }
}
