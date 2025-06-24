<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        try {
            $this->call([
                CentresSeeder::class,             // First, create all centres
                UsersSeeder::class,          // Second, create initial users
                TraineesSeeder::class,            // Create trainees
                ActivitiesSeeder::class,
            ]);
            
            // After seeding, diversify user centre distribution
            $this->command->info('Diversifying user centre distribution...');
            Artisan::call('centres:diversify');
            $this->command->info(Artisan::output());
            
        } catch (\Exception $e) {
            Log::error('Error in database seeding: ' . $e->getMessage());
            echo "Error seeding database: " . $e->getMessage() . "\n";
        }
    }
}