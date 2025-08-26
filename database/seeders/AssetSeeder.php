<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏭 Seeding assets...');

        $faker = Faker::create();
        $centres = DB::table('centres')->get();
        $assetTypes = DB::table('asset_types')->get();
        $categories = DB::table('asset_categories')->get();
        $locations = DB::table('asset_locations')->get();
        
        $totalAssets = 0;
        
        foreach ($centres as $centre) {
            $centreLocations = $locations->where('centre_id', $centre->centre_id);
            
            // Create 10-15 assets per centre
            for ($i = 1; $i <= 12; $i++) {
                $assetType = $assetTypes->random();
                $category = $categories->random();
                $location = $centreLocations->random();
                
                DB::table('assets')->insert([
                    'asset_tag' => $centre->centre_id . '-' . sprintf('%04d', $i),
                    'asset_name' => $this->generateAssetName($assetType->type_name, $faker),
                    'asset_description' => 'Asset for rehabilitation and educational purposes',
                    'type_id' => $assetType->id,
                    'category_id' => $category->id,
                    'centre_id' => $centre->centre_id,
                    'location_id' => $location->id,
                    'serial_number' => strtoupper($faker->bothify('??######')),
                    'model_number' => $faker->randomElement(['Model A', 'Model B', 'Model C']),
                    'manufacturer' => $faker->randomElement(['MedTech', 'EduSupply', 'TechAssist']),
                    'purchase_date' => $faker->dateTimeBetween('-3 years', '-1 month'),
                    'purchase_price' => $faker->randomFloat(2, 100, 5000),
                    'condition' => $faker->randomElement(['excellent', 'good', 'fair']),
                    'status' => $faker->randomElement(['available', 'in_use']),
                    'notes' => 'Regular maintenance required',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $totalAssets++;
            }
        }

        $this->command->info("🏭 Successfully seeded {$totalAssets} assets");
    }
    
    private function generateAssetName($typeName, $faker)
    {
        $names = [
            'Medical Equipment' => ['Therapy Ball', 'Exercise Mat', 'Balance Board', 'Resistance Bands'],
            'Educational Materials' => ['Learning Tablets', 'Puzzle Set', 'Flash Cards', 'Story Books'],
            'Technology Equipment' => ['Computer Desktop', 'Laptop', 'Tablet', 'Assistive Software'],
            'Furniture' => ['Therapy Chair', 'Adjustable Table', 'Storage Cabinet', 'Bookshelf']
        ];
        
        $typeNames = $names[$typeName] ?? ['Generic Equipment'];
        return $faker->randomElement($typeNames);
    }
}