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
                // Foundation data - no dependencies
                CentresSeeder::class,             // First, create all centres
                CREAMSUserSeeder::class,          // Create CREAMS staff users
                DisabilityAccommodationsSeeder::class, // Disability accommodations
                
                // Trainee data - depends on centres
                TraineesSeeder::class,            // Create trainees
                
                // Communication data
                MessagesSeeder::class,            // Internal messages
                NotificationsSeeder::class,       // System notifications
                
                // Activity data - core CREAMS functionality
                RehabilitationActivitiesSeeder::class, // Rehabilitation activities
            ]);
            
            // After seeding, diversify user centre distribution
            $this->command->info('Diversifying user centre distribution...');
            Artisan::call('centres:diversify');
            $this->command->info(Artisan::output());
            
            $this->command->info('CREAMS database seeding completed successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error in CREAMS database seeding: ' . $e->getMessage());
            echo "Error seeding CREAMS database: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}