<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏷️ Seeding asset types...');

        $assetTypes = [
            [
                'type_name' => 'Medical Equipment',
                'type_description' => 'Medical devices and equipment for rehabilitation therapy',
                'type_color' => '#e74c3c',
                'type_attributes' => json_encode(['requires_calibration' => true, 'maintenance_frequency' => 'monthly']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_name' => 'Educational Materials',
                'type_description' => 'Books, learning aids, and educational resources',
                'type_color' => '#3498db',
                'type_attributes' => json_encode(['requires_calibration' => false, 'maintenance_frequency' => 'yearly']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_name' => 'Technology Equipment',
                'type_description' => 'Computers, tablets, and assistive technology',
                'type_color' => '#9b59b6',
                'type_attributes' => json_encode(['requires_calibration' => true, 'maintenance_frequency' => 'quarterly']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_name' => 'Furniture',
                'type_description' => 'Chairs, tables, storage units, and specialized furniture',
                'type_color' => '#95a5a6',
                'type_attributes' => json_encode(['requires_calibration' => false, 'maintenance_frequency' => 'yearly']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($assetTypes as $type) {
            DB::table('asset_types')->insert($type);
        }

        $this->command->info('🏷️ Successfully seeded ' . count($assetTypes) . ' asset types');
    }
}