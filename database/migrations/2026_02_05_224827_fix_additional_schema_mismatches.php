<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix activity_schedules table if it exists
        if (Schema::hasTable('activity_schedules')) {
            Schema::table('activity_schedules', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_schedules', 'schedule_status')) {
                    $table->enum('schedule_status', ['active', 'cancelled', 'completed', 'pending'])->default('active')->after('end_time');
                }
            });
        }

        // Fix activity_enrollments table if it exists
        if (Schema::hasTable('activity_enrollments')) {
            Schema::table('activity_enrollments', function (Blueprint $table) {
                if (!Schema::hasColumn('activity_enrollments', 'progress_percentage')) {
                    $table->integer('progress_percentage')->default(0)->after('enrollment_status');
                }
            });
        }

        // Fix activity_sessions table - check for session_date column
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                // If 'date' column exists but not 'session_date', add session_date as alias
                if (Schema::hasColumn('activity_sessions', 'date') && !Schema::hasColumn('activity_sessions', 'session_date')) {
                    $table->date('session_date')->nullable()->after('date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_schedules')) {
            Schema::table('activity_schedules', function (Blueprint $table) {
                $table->dropColumn('schedule_status');
            });
        }

        if (Schema::hasTable('activity_enrollments')) {
            Schema::table('activity_enrollments', function (Blueprint $table) {
                $table->dropColumn('progress_percentage');
            });
        }

        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('activity_sessions', 'session_date')) {
                    $table->dropColumn('session_date');
                }
            });
        }
    }
};
