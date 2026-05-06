<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class MalaysianLocaleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set Malaysian timezone and locale for Carbon
        Carbon::setLocale('en');
        
        // Set Malaysian date format preferences
        Carbon::macro('toMalaysianDate', function () {
            return $this->format('d/m/Y');
        });
        
        Carbon::macro('toMalaysianDateTime', function () {
            return $this->format('d/m/Y h:i A');
        });
        
        Carbon::macro('toMalaysianTime', function () {
            return $this->format('h:i A');
        });

        // Carbon 3 defaults to ISO week (Monday start) — no explicit call needed.
    }
}
