<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\PaymentApi;
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
        $this->loadHelpers();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (\App::environment() !== 'development') {
            \URL::forceScheme('https');
        }

        $this->app->bind(PayPalHttpClient::class, function () {
            $payment_api = PaymentApi::getActivePaypal();
            $credentials = Setting::getValue([                                        
                    'instant_payment_paypal_mode',
            ]);
            $client_id = $payment_api->key;
            $secret = $payment_api->secret;
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

        $this->app['config']['sentry'] = ['dsn' => Setting::getValue('sentry_dsn')];

        if (isset($_COOKIE['DEBUG_COOKIE_KEY']) && $_COOKIE['DEBUG_COOKIE_KEY'] === \Config::get('app.debug_cookie_key')) {
            \Debugbar::enable();
            \DB::connection('mongodb')->enableQueryLog();
        }
    }

    protected function loadHelpers() {
        foreach (glob(__DIR__.'/../Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }
}