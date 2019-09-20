<?php

namespace App\Providers;

use App\Models\OdinCustomer;
use App\Services\PayPalService;
use App\Services\UtilsService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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


        View::composer('checkout', function($view) {
            $country = UtilsService::getLocationCountryCode();
            $recentlyBoughtNames = $recentlyBoughtCities = [];
            OdinCustomer::select(['first_name', 'last_name'])
                ->limit(25)
                ->get()
                ->each(function($item, $key) use ($country, &$recentlyBoughtNames) {
                    $recentlyBoughtNames[] = $item['first_name'] . ' ' . $item['last_name'];
                });

            OdinCustomer::where(['addresses.country' => $country])
                ->distinct('addresses.city')
                ->limit(25)
                ->get()
                ->each(function($item, $key) use ($country, &$recentlyBoughtCities) {
                    $recentlyBoughtCities[] = collect($item)->first();
                });

            $notification_data = [
                'recentlyBoughtNames' => $recentlyBoughtNames,
                'recentlyBoughtCities' => $recentlyBoughtCities
            ];

            $view->with('notificationData', $notification_data);
        });
    }
}
