<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Midtrans\Config;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Config::$isProduction = false;
        // Config::$serverKey = config('services.midtrans.server_key');

        // Config::$curlOptions = [
        //     CURLOPT_CAINFO => 'C:/wamp64/bin/php/php8.2.26/extras/ssl/cacert.pem',
        // ];
        Config::$curlOptions = [
            CURLOPT_CAINFO => base_path('cacert.pem'),
            CURLOPT_HTTPHEADER => [],
        ];
        RateLimiter::for('blast-limiter', function ($job) {
            return Limit::perMinutes(2, 9);
        });
    }
}
