<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryExecuted;

class PerformanceServiceProvider extends ServiceProvider
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
        // Enable query logging in development
        if (config('app.debug')) {
            DB::listen(function (QueryExecuted $query) {
                if ($query->time > 1000) { // Log slow queries (>1s)
                    Log::warning('Slow Query Detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }

        // Enable Eloquent lazy loading prevention in development
        if (config('app.env') === 'local') {
            \Illuminate\Database\Eloquent\Model::preventLazyLoading(true);
        }
    }
}
