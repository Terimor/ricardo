<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(PayPalHttpClient::class, function () {
            $credentials = Setting::whereIn(
                'key',
                ['instant_payment_paypal_client_id', 'instant_payment_paypal_secret']
            )->get();
            $client_id = optional($credentials->where('key', 'instant_payment_paypal_client_id')->first())->value;
            $secret = optional($credentials->where('key', 'instant_payment_paypal_secret')->first())->value;
            if(config('services.paypal.mode') === 'sandbox') {
                $env = new SandboxEnvironment($client_id, $secret);
            } else {
                $env = new ProductionEnvironment($client_id, $secret);
            }
            return new PayPalHttpClient($env);
        });

        if (config('app.debug')){
            \DB::connection('mongodb')->enableQueryLog();
        }
    }
}
