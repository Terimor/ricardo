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
            $credentials = Setting::whereIn(
                'key',
                [
                    'instant_payment_paypal_client_id',
                    'instant_payment_paypal_secret',
                    'instant_payment_paypal_mode',
                ]
            )->get();
            $client_id = optional($credentials->where('key', 'instant_payment_paypal_client_id')->first())->value;
            $secret = optional($credentials->where('key', 'instant_payment_paypal_secret')->first())->value;

            $client_id = 'Acb-X-ffLLPVtCPyyzLsmRPVMu_veR_r3JlLQr2-w0DNlzLEWYg-w25Zv0R796o1dlGt4olNcC6lkBGk';
            $secret = 'EHrQ7FD0AnK1vw7Qg-hTtA0y7npO6sTvjoIGGm27J43_GxUHK31NBa_kpXzhjCPclKIBUj4Y1TTAvdU1';

            $mode = optional($credentials->where('key', 'instant_payment_paypal_mode')->first())->value;
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


        $sentryDNS = \Utils::getSetting('sentry_dsn');
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
