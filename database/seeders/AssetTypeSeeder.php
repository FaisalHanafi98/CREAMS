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
                'category_id' => 1,
                'requires_maintenance' => true,
                'default_maintenance_interval_days' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_name' => 'Educational Materials',
                'type_description' => 'Books, learning aids, and educational resources',
                'category_id' => 2,
                'requires_maintenance' => false,
                'default_maintenance_interval_days' => 365,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_name' => 'Technology Equipment',
                'type_description' => 'Computers, tablets, and assistive technology',
                'category_id' => 3,
                'requires_maintenance' => true,
                'default_maintenance_interval_days' => 90,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'type_name' => 'Furniture',
                'type_description' => 'Chairs, tables, storage units, and specialized furniture',
                'category_id' => 4,
                'requires_maintenance' => false,
                'default_maintenance_interval_days' => 365,
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