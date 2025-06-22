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

        // Register routes
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
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