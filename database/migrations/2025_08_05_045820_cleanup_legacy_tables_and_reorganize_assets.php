<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        echo "🗑️ Cleaning up legacy tables...\n";
        
        // 1. Drop legacy academic system tables
        $legacyTables = [
            'courses',      // Old academic system - replaced by activity categories
            'classes',      // Old academic system - replaced by activity sessions
            'sessions'      // Laravel default - using database sessions instead
        ];
        
        foreach ($legacyTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
                echo "   ✅ Dropped legacy table: $table\n";
            }
        }
        
        echo "🔄 Reorganizing asset management system...\n";
        
        // 2. Asset table reorganization
        if (Schema::hasTable('assets_enhanced') && Schema::hasTable('assets')) {
            // Drop old simple assets table
            Schema::dropIfExists('assets');
            echo "   ✅ Dropped old assets table\n";
            
            // Rename assets_enhanced to assets (making it the main table)
            DB::statement('RENAME TABLE assets_enhanced TO assets');
            echo "   ✅ Renamed assets_enhanced to assets (now the main asset table)\n";
            
            // Update foreign key references in related tables
            $this->updateAssetForeignKeys();
        } elseif (Schema::hasTable('assets_enhanced') && !Schema::hasTable('assets')) {
            // Just rename if old assets table doesn't exist
            DB::statement('RENAME TABLE assets_enhanced TO assets');
            echo "   ✅ Renamed assets_enhanced to assets\n";
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "✅ Database cleanup and reorganization completed!\n";
    }

    /**
     * Update foreign key references to point to the new assets table
     */
    private function updateAssetForeignKeys(): void
    {
        try {
            // Update asset_maintenance table foreign key
            if (Schema::hasTable('asset_maintenance')) {
                // Drop existing foreign key
                DB::statement('ALTER TABLE asset_maintenance DROP FOREIGN KEY asset_maintenance_asset_id_foreign');
                
                // Add new foreign key pointing to assets table
                DB::statement('ALTER TABLE asset_maintenance ADD CONSTRAINT asset_maintenance_asset_id_foreign FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE');
                echo "   ✅ Updated asset_maintenance foreign key\n";
            }
            
            // Update asset_movements table foreign key
            if (Schema::hasTable('asset_movements')) {
                // Drop existing foreign key
                DB::statement('ALTER TABLE asset_movements DROP FOREIGN KEY asset_movements_asset_id_foreign');
                
                // Add new foreign key pointing to assets table
                DB::statement('ALTER TABLE asset_movements ADD CONSTRAINT asset_movements_asset_id_foreign FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE');
                echo "   ✅ Updated asset_movements foreign key\n";
            }
        } catch (\Exception $e) {
            echo "   ⚠️ Foreign key update warning: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "⚠️ This cleanup migration cannot be safely reversed.\n";
        echo "   The legacy tables and old asset structure would need to be recreated manually.\n";
    }
};
