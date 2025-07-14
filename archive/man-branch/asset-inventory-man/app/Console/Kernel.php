<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Register your commands here if needed
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Schedule avatar sync to run weekly to ensure consistency
        $schedule->command('avatars:sync')->weekly();
        
        // Schedule centre sync to run daily to ensure consistency
        $schedule->command('centres:sync')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
    
    /**
     * The application's route middleware.
     *
     * Note: This should actually be in your Http Kernel, not Console Kernel.
     * This is causing confusion in your implementation.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // other middleware...
        'auth' => \App\Http\Middleware\Authenticate::class,
    ];
}