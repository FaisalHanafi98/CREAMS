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
        // Add scheduled_date column to activity_sessions table
        if (Schema::hasTable('activity_sessions') && !Schema::hasColumn('activity_sessions', 'scheduled_date')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $table->date('scheduled_date')->nullable()->after('session_description');
                $table->index('scheduled_date');
            });
            
            // Populate scheduled_date with session_date values
            DB::statement('UPDATE activity_sessions SET scheduled_date = session_date WHERE scheduled_date IS NULL');
            
            // Make scheduled_date not nullable after populating
            Schema::table('activity_sessions', function (Blueprint $table) {
                $table->date('scheduled_date')->nullable(false)->change();
            });
        }
        
        // Add scheduled_date column to activity_sessions_new table if it exists
        if (Schema::hasTable('activity_sessions_new') && !Schema::hasColumn('activity_sessions_new', 'scheduled_date')) {
            Schema::table('activity_sessions_new', function (Blueprint $table) {
                $table->date('scheduled_date')->nullable()->after('session_code');
                $table->index('scheduled_date');
            });
            
            // Populate scheduled_date with session_date values
            DB::statement('UPDATE activity_sessions_new SET scheduled_date = session_date WHERE scheduled_date IS NULL');
            
            // Make scheduled_date not nullable after populating
            Schema::table('activity_sessions_new', function (Blueprint $table) {
                $table->date('scheduled_date')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_sessions') && Schema::hasColumn('activity_sessions', 'scheduled_date')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $table->dropIndex(['scheduled_date']);
                $table->dropColumn('scheduled_date');
            });
        }
        
        if (Schema::hasTable('activity_sessions_new') && Schema::hasColumn('activity_sessions_new', 'scheduled_date')) {
            Schema::table('activity_sessions_new', function (Blueprint $table) {
                $table->dropIndex(['scheduled_date']);
                $table->dropColumn('scheduled_date');
            });
        }
    }
};