<?php

namespace App\Providers;

use App\Extensions\MongoStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
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
        Cache::extend('redis', function ($app) {
            return Cache::repository(new MongoStore(
                $app['redis'],
                $app['config']['cache.prefix'],
                $app['config']['cache.stores.redis.connection']
            ));
        });
    }
}
