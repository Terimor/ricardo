<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\I18nService;
use Illuminate\Support\ServiceProvider;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadHelpers();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(PayPalHttpClient::class, function () {
            $credentials = Setting::getValue([
                    'instant_payment_paypal_client_id',
                    'instant_payment_paypal_secret',
                    'instant_payment_paypal_mode',
                ]);
            $client_id = $credentials['instant_payment_paypal_client_id'];
            $secret = $credentials['instant_payment_paypal_secret'];
            $mode = $credentials['instant_payment_paypal_mode'];
            if($mode === 'sandbox') {
                $env = new SandboxEnvironment($client_id, $secret);
            } else {
                $env = new ProductionEnvironment($client_id, $secret);
            }
            return new PayPalHttpClient($env);
        });

        if (config('app.debug')){
            \DB::connection('mongodb')->enableQueryLog();
        }


        $sentryDNS = Setting::getValue('sentry_dsn');
        $this->app['config']['sentry'] = [
            'dsn' => $sentryDNS ? $sentryDNS : env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN'))
        ];

    }

    protected function loadHelpers() {
        foreach (glob(__DIR__.'/../Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }
}
