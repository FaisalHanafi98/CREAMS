<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CREAMSSeederAssetManagement extends Seeder
{
    /**
     * CREAMS Asset Management Seeder
     * Seeds: Asset categories, types, locations, assets, maintenance
     */
    public function run(): void
    {
        $this->command->info('🔧 Seeding CREAMS Asset Management...');
        
        // Restore real Gombak assets if backup exists
        if (file_exists(database_path('real_data_backup.json'))) {
            $this->seedRealGombakAssets();
        }
        
        $this->call([
            AssetCategorySeeder::class,
            AssetTypeSeeder::class,
            AssetLocationSeeder::class,
            AssetSeeder::class,
            AssetMaintenanceSeeder::class,
        ]);
        
        $this->command->info('✅ Asset Management seeding completed');
    }

    /**
     * Restore real Gombak assets from backup
     */
    private function seedRealGombakAssets(): void
    {
        $this->command->info('   🏗️ Restoring real Gombak assets...');
        
        $realData = json_decode(file_get_contents(database_path('real_data_backup.json')), true);
        
        if (isset($realData['assets']) && !empty($realData['assets'])) {
            foreach ($realData['assets'] as $asset) {
                // Convert array to proper format and preserve all fields
                $assetData = (array) $asset;
                try {
                    DB::table('assets')->insertOrIgnore($assetData);
                } catch (\Exception $e) {
                    // Skip duplicates or errors
                }
            }
            
            $this->command->line('      ✓ Restored ' . count($realData['assets']) . ' real Gombak assets');
        } else {
            $this->command->line('      ⚠ No real asset data found in backup');
        }
    }
}