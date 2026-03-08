<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     * This path will be used by the built-in authentication services.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Configure rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Authentication rate limiters (CRITICAL SECURITY)
        RateLimiter::for('login', function (Request $request) {
            // Use 'email' first (set by global-setup), then 'identifier' (browser form field),
            // then fall back to IP — ensures per-user buckets for all login paths.
            $key = $request->input('email') ?: $request->input('identifier') ?: $request->ip();
            return [
                Limit::perMinute(env('RATE_LIMIT_LOGIN', 5))->by($key)->response(function () {
                    return redirect()->back()->withErrors([
                        'email' => 'Too many login attempts. Please try again in 1 minute.'
                    ]);
                }),
            ];
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $key = $request->input('email') ?: $request->ip();
            return [
                Limit::perMinute(3)->by($key)->response(function () {
                    return redirect()->back()->withErrors([
                        'email' => 'Too many password reset requests. Please try again in 1 minute.'
                    ]);
                }),
                Limit::perHour(10)->by($key),
            ];
        });

        RateLimiter::for('registration', function (Request $request) {
            return [
                Limit::perMinute(3)->by($request->ip())->response(function () {
                    return redirect()->back()->withErrors([
                        'email' => 'Too many registration attempts. Please try again in 1 minute.'
                    ]);
                }),
                Limit::perHour(5)->by($request->ip()),
            ];
        });

        // Dashboard-specific rate limiters for optimized performance
        RateLimiter::for('dashboard', function (Request $request) {
            $userId = session('id') ?: $request->ip();
            return [
                Limit::perMinute(120)->by($userId), // 120 requests per minute for dashboard
                Limit::perHour(1000)->by($userId),  // 1000 requests per hour
            ];
        });

        RateLimiter::for('dashboard-updates', function (Request $request) {
            $userId = session('id') ?: $request->ip();
            return [
                Limit::perMinute(30)->by($userId),  // 30 AJAX updates per minute
                Limit::perHour(600)->by($userId),   // 600 updates per hour
            ];
        });

        RateLimiter::for('dashboard-refresh', function (Request $request) {
            $userId = session('id') ?: $request->ip();
            return [
                Limit::perMinute(10)->by($userId),  // 10 manual refreshes per minute
                Limit::perHour(120)->by($userId),   // 120 refreshes per hour
            ];
        });

        RateLimiter::for('export', function (Request $request) {
            $userId = session('id') ?: $request->ip();
            return [
                Limit::perMinute(5)->by($userId),   // 5 exports per minute
                Limit::perHour(50)->by($userId),    // 50 exports per hour
            ];
        });

        RateLimiter::for('admin-actions', function (Request $request) {
            $userId = session('id') ?: $request->ip();
            return [
                Limit::perMinute(20)->by($userId),  // 20 admin actions per minute
                Limit::perHour(200)->by($userId),   // 200 admin actions per hour
            ];
        });

        // Register routes
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Demo instance routes: /creams/{demo_id}/*
            // This allows multiple isolated demo instances
            Route::middleware(['web', 'demo'])
                ->prefix('creams/{demo_id}')
                ->group(base_path('routes/web.php'));

            // Also keep direct access for local development
            // Comment out this block in production if you only want /creams/{demo_id}/ URLs
            if (app()->environment('local', 'testing')) {
                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            }
        });
    }

    /**
     * Get the redirect path based on user role.
     * 
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function redirectTo(Request $request)
    {
        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Determine user's role
            $userRole = null;
            
            // Check which class the user belongs to
            $className = get_class($user);
            if (strpos($className, 'Admin') !== false) {
                $userRole = 'admin';
            } elseif (strpos($className, 'Supervisor') !== false) {
                $userRole = 'supervisor';
            } elseif (strpos($className, 'Teacher') !== false) {
                $userRole = 'teacher';
            } elseif (strpos($className, 'Trainee') !== false || strpos($className, 'Trainee') !== false) {
                $userRole = 'trainee';
            }
            
            // If we couldn't determine role from class, check role property
            if (!$userRole && isset($user->role)) {
                $userRole = strtolower($user->role);
            }
            
            // Return appropriate dashboard based on determined role
            switch ($userRole) {
                case 'admin':
                    return route('admin.dashboard');
                case 'supervisor':
                    return route('supervisor.dashboard');
                case 'teacher':
                    return route('teacher.dashboard');
                case 'trainee':
                    return route('trainee.dashboard');
                default:
                    // Default dashboard if role not matched
                    return self::HOME;
            }
        }
        
        // If not authenticated, return to home page
        return route('home');
    }
}