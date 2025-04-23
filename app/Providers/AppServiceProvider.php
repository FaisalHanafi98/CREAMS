<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share user data with all views
        View::composer('*', function ($view) {
            // Only execute if user is logged in (session has id and role)
            if (session()->has('id') && session()->has('role')) {
                try {
                    // Share only the essential user data needed across views
                    $userData = [
                        'id' => session('id'),
                        'name' => session('name'),
                        'role' => session('role'),
                        'email' => session('email'),
                        'centre_id' => session('centre_id')
                    ];
                    
                    // Add avatar if it exists in session
                    if (session()->has('avatar')) {
                        $userData['avatar'] = session('avatar');
                    } elseif (session()->has('user_avatar')) {
                        $userData['avatar'] = session('user_avatar');
                    }
                    
                    // Share the user data with the view
                    $view->with('user', $userData);
                    
                    // Log successful data sharing for debugging
                    // Log::debug('User data shared with view', ['view' => $view->getName()]);
                } catch (\Exception $e) {
                    // Log error but don't crash the application
                    Log::error('Error sharing user data with view', [
                        'error' => $e->getMessage(),
                        'view' => $view->getName()
                    ]);
                    
                    // Provide empty user data to prevent view errors
                    $view->with('user', [
                        'id' => null,
                        'name' => 'Guest',
                        'role' => 'guest',
                        'email' => null,
                        'centre_id' => null,
                        'avatar' => null
                    ]);
                }
            } else {
                // If no user is logged in, provide default guest data
                $view->with('user', [
                    'id' => null,
                    'name' => 'Guest',
                    'role' => 'guest',
                    'email' => null,
                    'centre_id' => null,
                    'avatar' => null
                ]);
            }
        });
    }
}
