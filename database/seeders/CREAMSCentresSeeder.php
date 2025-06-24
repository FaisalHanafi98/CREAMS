<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Centres;

class CREAMSCentresSeeder extends Seeder
{
    public function run(): void
    {
        $centres = [
            [
                'centre_id' => '01',
                'centre_name' => 'Gombak',
                'address' => 'Jalan Gombak, Selangor',
                'city' => 'Gombak',
                'state' => 'Selangor',
                'postcode' => '53100',
                'phone' => '03-1234567',
                'email' => 'gombak@creams.edu.my',
                'capacity' => 150,
                'description' => 'CREAMS Gombak Centre - Main rehabilitation centre providing comprehensive special needs education and therapy services.',
                'facilities' => '["Therapy Rooms", "Sensory Integration Room", "Computer Lab", "Library", "Art Therapy Room", "Gymnasium"]',
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'is_active' => 1,
                'status' => 'active'
            ],
            [
                'centre_id' => '02',
                'centre_name' => 'Kuantan',
                'address' => 'Jalan Pahang, Kuantan',
                'city' => 'Kuantan',
                'state' => 'Pahang',
                'postcode' => '25200',
                'phone' => '09-2345678',
                'email' => 'kuantan@creams.edu.my',
                'capacity' => 120,
                'description' => 'CREAMS Kuantan Centre - Specialized centre focusing on autism spectrum disorders and developmental disabilities.',
                'facilities' => '["Speech Therapy Room", "Occupational Therapy Room", "Sensory Garden", "Learning Center", "Music Therapy Room"]',
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'is_active' => 1,
                'status' => 'active'
            ],
            [
                'centre_id' => '03',
                'centre_name' => 'Pagoh',
                'address' => 'Jalan Pagoh, Johor',
                'city' => 'Pagoh',
                'state' => 'Johor',
                'postcode' => '84600',
                'phone' => '07-3456789',
                'email' => 'pagoh@creams.edu.my',
                'capacity' => 100,
                'description' => 'CREAMS Pagoh Centre - Community-based rehabilitation centre with focus on vocational training and life skills development.',
                'facilities' => '["Vocational Training Workshop", "Life Skills Kitchen", "Computer Training Center", "Counseling Rooms", "Recreation Hall"]',
                'opening_time' => '08:00',
                'closing_time' => '17:00',
                'is_active' => 1,
                'status' => 'active'
            ]
        ];

        foreach ($centres as $centre) {
            Centres::updateOrCreate(
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