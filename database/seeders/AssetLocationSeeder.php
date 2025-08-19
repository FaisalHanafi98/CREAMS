<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetLocationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📍 Seeding asset locations...');

        $centres = DB::table('centres')->get();
        $totalLocations = 0;
        
        foreach ($centres as $centre) {
            $locations = [
                ['location_name' => 'Therapy Room 1', 'building' => 'Main', 'floor' => 'Ground', 'room' => 'T101'],
                ['location_name' => 'Therapy Room 2', 'building' => 'Main', 'floor' => 'Ground', 'room' => 'T102'],
                ['location_name' => 'Computer Lab', 'building' => 'Main', 'floor' => 'First', 'room' => 'C201'],
                ['location_name' => 'Administrative Office', 'building' => 'Main', 'floor' => 'First', 'room' => 'A202'],
                ['location_name' => 'Storage Room', 'building' => 'Main', 'floor' => 'Ground', 'room' => 'S103']
            ];
            
            foreach ($locations as $location) {
                DB::table('asset_locations')->insert(array_merge($location, [
                    'centre_id' => $centre->centre_id,
                    'location_description' => "Location at {$centre->centre_name} centre",
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]));
                $totalLocations++;
            }
        }

        $this->command->info("📍 Successfully seeded {$totalLocations} asset locations");
    }
}