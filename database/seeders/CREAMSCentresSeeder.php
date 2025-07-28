<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Centre;

class CREAMSCentresSeeder extends Seeder
{
    public function run(): void
    {
        $centres = [
            [
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'centre_address' => 'Jalan Gombak, Selangor',
                'centre_phone' => '03-1234567',
                'centre_email' => 'gombak@creams.edu.my',
                'centre_capacity' => '150',
                'centre_manager' => 'Dr. Ahmad bin Ali',
                'centre_manager_contact' => '03-1234567',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Gombak Centre - Main rehabilitation centre providing comprehensive special needs education and therapy services.',
                'centre_facilities' => '["Therapy Rooms", "Sensory Integration Room", "Computer Lab", "Library", "Art Therapy Room", "Gymnasium"]',
                'is_active' => 1
            ],
            [
                'centre_id' => '02',
                'centre_name' => 'Kuantan',
                'centre_address' => 'Jalan Pahang, Kuantan',
                'centre_phone' => '09-2345678',
                'centre_email' => 'kuantan@creams.edu.my',
                'centre_capacity' => '120',
                'centre_manager' => 'Dr. Siti Fatimah',
                'centre_manager_contact' => '09-2345678',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Kuantan Centre - Specialized centre focusing on autism spectrum disorders and developmental disabilities.',
                'centre_facilities' => '["Speech Therapy Room", "Occupational Therapy Room", "Sensory Garden", "Learning Center", "Music Therapy Room"]',
                'is_active' => 1
            ],
            [
                'centre_id' => '03',
                'centre_name' => 'Pagoh',
                'centre_address' => 'Jalan Pagoh, Johor',
                'centre_phone' => '07-3456789',
                'centre_email' => 'pagoh@creams.edu.my',
                'centre_capacity' => '100',
                'centre_manager' => 'Dr. Mohd Hazlan',
                'centre_manager_contact' => '07-3456789',
                'centre_status' => 'active',
                'centre_description' => 'CREAMS Pagoh Centre - Community-based rehabilitation centre with focus on vocational training and life skills development.',
                'centre_facilities' => '["Vocational Training Workshop", "Life Skills Kitchen", "Computer Training Center", "Counseling Rooms", "Recreation Hall"]',
                'is_active' => 1
            ]
        ];

        foreach ($centres as $centre) {
            Centre::updateOrCreate(
                ['centre_id' => $centre['centre_id']],
                $centre
            );
        }

        $this->command->info('CREAMS centres created successfully:');
        $this->command->info('- Gombak Centre (Main)');
        $this->command->info('- Kuantan Centre (Specialized)');
        $this->command->info('- Pagoh Centre (Community-based)');
    }
}