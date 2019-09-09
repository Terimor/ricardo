<?php

namespace App\Providers;

use App\Services\PayPalService;
use App\Services\UtilsService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
            $view->with('PayPalCurrency', UtilsService::getPayPalCurrencyCode());
        });
    }
}
