<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            \App\Contracts\ScanEngineClient::class,
            \App\Services\ScanEngine\FakeScanEngineClient::class // Ganti ke HttpScanEngineClient nanti saat Yasa siap
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
