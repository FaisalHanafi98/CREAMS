<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CREAMSSeederCommunicationManagement extends Seeder
{
    /**
     * CREAMS Communication Management Seeder
     * Seeds: Messages, notifications, letters, templates
     */
    public function run(): void
    {
        $this->command->info('📨 Seeding CREAMS Communication Management...');
        
        $this->call([
            MessageSeeder::class,
            NotificationSeeder::class,
            LetterSeeder::class,
            LetterTemplateSeeder::class,
        ]);
        
        $this->command->info('✅ Communication Management seeding completed');
    }
}