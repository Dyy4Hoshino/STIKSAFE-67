<?php

namespace App\Providers;

use App\Contracts\ScanEngineClient;
use App\Services\ScanEngine\FakeScanEngineClient;
use App\Services\ScanEngine\HttpScanEngineClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ScanEngineClient::class,
            config('services.ml.driver') === 'http'
                ? HttpScanEngineClient::class
                : FakeScanEngineClient::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
