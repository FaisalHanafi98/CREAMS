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
        
        // Drop incorrect foreign key constraints using Laravel's safe method
        if (Schema::hasTable('activity_schedules')) {
            try {
                Schema::table('activity_schedules', function (Blueprint $table) {
                    $table->dropForeign(['activity_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        if (Schema::hasTable('activity_sessions')) {
            try {
                Schema::table('activity_sessions', function (Blueprint $table) {
                    $table->dropForeign(['activity_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        if (Schema::hasTable('activity_enrollments')) {
            try {
                Schema::table('activity_enrollments', function (Blueprint $table) {
                    $table->dropForeign(['activity_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        // Add correct foreign key constraints pointing to activities table
        if (Schema::hasTable('activity_schedules')) {
            try {
                Schema::table('activity_schedules', function (Blueprint $table) {
                    $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, continue
            }
        }
        
        if (Schema::hasTable('activity_sessions')) {
            try {
                Schema::table('activity_sessions', function (Blueprint $table) {
                    $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, continue
            }
        }
        
        if (Schema::hasTable('activity_enrollments')) {
            try {
                Schema::table('activity_enrollments', function (Blueprint $table) {
                    $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist, continue
            }
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
