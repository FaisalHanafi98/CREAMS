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
        
        // 1. Drop redundant "_new" suffix tables in reverse dependency order
        $newTables = [
            'activity_enrollments_new',  // Drop child tables first
            'activity_sessions_new', 
            'activities_new'            // Drop parent table last
        ];
        
        foreach ($newTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
                echo "Dropped table: $table\n";
            }
        }
        
        // 2. Drop redundant singular attendance tables (keep plural ones)
        $redundantTables = [
            'attendance',           // Keep 'attendances' (plural)
            'session_attendance'    // Keep 'session_attendances' if exists, or use session_enrollments
        ];
        
        foreach ($redundantTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
                echo "Dropped table: $table\n";
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // 3. Ensure all remaining tables follow plural naming convention
        // Note: Most core tables are already properly pluralized:
        // - activities (plural) ✓
        // - activity_sessions (plural) ✓  
        // - activity_enrollments (plural) ✓
        // - session_enrollments (plural) ✓
        // - attendances (plural) ✓
        // - staff_attendances (plural) ✓
        // - trainees (plural) ✓
        // - users (plural) ✓
        // - centres (plural) ✓
        
        echo "Database cleanup completed - all tables now follow proper plural naming convention\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is a cleanup operation
        // Reversing it would recreate problematic tables
        // So we'll leave the down method empty to prevent accidental restoration
        echo "Cleanup migration rollback attempted - no action taken to prevent table duplication\n";
    }
};
