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
        // Register Malaysian date helper functions
        if (!function_exists('malaysianDate')) {
            function malaysianDate($date = null) {
                $carbon = $date ? \Carbon\Carbon::parse($date) : now();
                return $carbon->toMalaysianDate();
            }
        }
        
        if (!function_exists('malaysianDateTime')) {
            function malaysianDateTime($date = null) {
                $carbon = $date ? \Carbon\Carbon::parse($date) : now();
                return $carbon->toMalaysianDateTime();
            }
        }
        
        if (!function_exists('malaysianTime')) {
            function malaysianTime($date = null) {
                $carbon = $date ? \Carbon\Carbon::parse($date) : now();
                return $carbon->toMalaysianTime();
            }
        }
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
                    } 
                    
                    // Only set user data if it's not already set by the controller
                    // This prevents overriding detailed user data from controllers like UserProfileController
                    if (!$view->getData() || !array_key_exists('user', $view->getData())) {
                        $view->with('user', $userData);
                    } else {
                        // If user data already exists, make session data available as 'sessionUser'
                        $view->with('sessionUser', $userData);
                    }
                    
                    // Log successful data sharing for debugging
                    // Log::debug('User data shared with view', ['view' => $view->getName()]);
                } catch (\Exception $e) {
                    // Log error but don't crash the application
                    Log::error('Error sharing user data with view', [
                        'error' => $e->getMessage(),
                        'view' => $view->getName()
                    ]);
                    
                    // Provide empty user data to prevent view errors
                    if (!$view->getData() || !array_key_exists('user', $view->getData())) {
                        $view->with('user', [
                            'id' => null,
                            'name' => 'Guest',
                            'role' => 'guest',
                            'email' => null,
                            'centre_id' => null,
                            'avatar' => null
                        ]);
                    }
                }
            } else {
                // If no user is logged in, provide default guest data
                if (!$view->getData() || !array_key_exists('user', $view->getData())) {
                    $view->with('user', [
                        'id' => null,
                        'name' => 'Guest',
                        'role' => 'guest',
                        'email' => null,
                        'centre_id' => null,
                        'avatar' => null
                    ]);
                }
            }
        });
    }
}
