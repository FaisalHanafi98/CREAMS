<?php

namespace Database\Seeders\Test;

use Illuminate\Database\Seeder;
use App\Models\Centre;

class CentreTestSeeder extends Seeder
{
    /**
     * Seed test centres across Malaysia.
     *
     * Creates 4 representative centres:
     * - Gombak (main test centre)
     * - Kuantan
     * - Kuala Lumpur
     * - Pagoh
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Seeding test centres...');

        $centres = [
            [
                'centre_id' => '01',
                'centre_name' => 'PPDK Gombak',
                'centre_phone' => '+60361964000',
                'centre_email' => 'gombak@ppdk.test',
                'centre_address' => 'Jalan Gombak, 53100 Kuala Lumpur',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ],
            [
                'centre_id' => '02',
                'centre_name' => 'PPDK Kuantan',
                'centre_phone' => '+60959555000',
                'centre_email' => 'kuantan@ppdk.test',
                'centre_address' => 'Jalan Beserah, 25200 Kuantan, Pahang',
                'centre_capacity' => 40,
                'centre_status' => 'active',
                'is_active' => true,
            ],
            [
                'centre_id' => '03',
                'centre_name' => 'PPDK Kuala Lumpur',
                'centre_phone' => '+60326937000',
                'centre_email' => 'kl@ppdk.test',
                'centre_address' => 'Jalan Ampang, 50450 Kuala Lumpur',
                'centre_capacity' => 60,
                'centre_status' => 'active',
                'is_active' => true,
            ],
            [
                'centre_id' => '04',
                'centre_name' => 'PPDK Pagoh',
                'centre_phone' => '+60697843000',
                'centre_email' => 'pagoh@ppdk.test',
                'centre_address' => 'KM 1, Jalan Panchor, 84600 Pagoh, Johor',
                'centre_capacity' => 35,
                'centre_status' => 'active',
                'is_active' => true,
            ],
        ];

        foreach ($centres as $centreData) {
            Centre::firstOrCreate(
                ['centre_id' => $centreData['centre_id']],
                $centreData
            );
        }

        $this->command->info('✓ Created 4 test centres');
    }
}
