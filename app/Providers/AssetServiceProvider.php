<?php

namespace App\Providers;

use App\Services\Asset\AssetManagementService;
use Illuminate\Support\ServiceProvider;

class AssetServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AssetManagementService::class, function ($app) {
            return new AssetManagementService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}