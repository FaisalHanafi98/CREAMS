<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Extensions\MultipleUserGuard;

class CustomAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Auth::extend('multiple-user', function ($app, $name, array $config) {
            return new MultipleUserGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['session.store'],
                $app['request']
            );
        });
    }
}