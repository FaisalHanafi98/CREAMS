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
        // Fix foreign key constraints that incorrectly reference activities_new instead of activities
        
        // Drop incorrect foreign key constraints
        if (Schema::hasTable('activity_schedules')) {
            DB::statement('ALTER TABLE activity_schedules DROP FOREIGN KEY IF EXISTS activity_schedules_activity_id_foreign');
        }
        
        if (Schema::hasTable('activity_sessions')) {
            DB::statement('ALTER TABLE activity_sessions DROP FOREIGN KEY IF EXISTS activity_sessions_activity_id_foreign');
        }
        
        if (Schema::hasTable('activity_enrollments')) {
            DB::statement('ALTER TABLE activity_enrollments DROP FOREIGN KEY IF EXISTS activity_enrollments_activity_id_foreign');
        }
        
        // Add correct foreign key constraints pointing to activities table
        if (Schema::hasTable('activity_schedules')) {
            Schema::table('activity_schedules', function (Blueprint $table) {
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            });
        }
        
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            });
        }
        
        if (Schema::hasTable('activity_enrollments')) {
            Schema::table('activity_enrollments', function (Blueprint $table) {
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the correct foreign key constraints
        if (Schema::hasTable('activity_schedules')) {
            Schema::table('activity_schedules', function (Blueprint $table) {
                $table->dropForeign(['activity_id']);
            });
        }
        
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $table->dropForeign(['activity_id']);
            });
        }
        
        if (Schema::hasTable('activity_enrollments')) {
            Schema::table('activity_enrollments', function (Blueprint $table) {
                $table->dropForeign(['activity_id']);
            });
        }
    }
};
