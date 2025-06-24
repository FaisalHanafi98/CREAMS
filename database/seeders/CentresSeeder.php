<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Centres;

class CentresSeeder extends Seeder
{
    public function run()
    {
        $centres = [
            [
                'centre_id' => 'CTR001',
                'centre_name' => 'Main Rehabilitation Centre',
                'address' => 'Jalan Merdeka 123',
                'city' => 'Kuala Lumpur',
                'state' => 'Wilayah Persekutuan',
                'postcode' => '50450',
                'phone' => '03-1234-5678',
                'email' => 'main@creams.edu.my',
                'capacity' => 150,
                'description' => 'Our flagship centre with comprehensive rehabilitation facilities',
                'facilities' => json_encode(['Therapy Pool', 'Sensory Room', 'Computer Lab', 'Art Studio', 'Gym']),
                'opening_time' => '08:00',
                'closing_time' => '18:00',
                'is_active' => true
            ],
            [
                'centre_id' => 'CTR002',
                'centre_name' => 'Shah Alam Branch',
                'address' => 'Persiaran Perdana 45',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'postcode' => '40000',
                'phone' => '03-5567-8901',
                'email' => 'shahalam@creams.edu.my',
                'capacity' => 100,
                'description' => 'Modern facility focusing on pediatric rehabilitation',
                'facilities' => json_encode(['Play Therapy Room', 'Speech Lab', 'Occupational Therapy Suite']),
                'opening_time' => '08:30',
                'closing_time' => '17:30',
                'is_active' => true
            ],
            [
                'centre_id' => 'CTR003',
                'centre_name' => 'Penang Centre',
                'address' => 'Jalan Georgetown 78',
                'city' => 'Georgetown',
                'state' => 'Pulau Pinang',
                'postcode' => '10200',
                'phone' => '04-2234-5678',
                'email' => 'penang@creams.edu.my',
                'capacity' => 80,
                'description' => 'Specialized centre for autism and behavioral therapy',
                'facilities' => json_encode(['Behavioral Therapy Rooms', 'Quiet Zones', 'Parent Training Room']),
                'opening_time' => '09:00',
                'closing_time' => '17:00',
                'is_active' => true
            ]
        ];

        foreach ($centres as $centre) {
            Centres::create($centre);
        }
    }
}