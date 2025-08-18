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
        // Check if activity_enrollments table exists and add missing columns
        if (Schema::hasTable('activity_enrollments')) {
            Schema::table('activity_enrollments', function (Blueprint $table) {
                // Add progress_percentage column if it doesn't exist
                if (!Schema::hasColumn('activity_enrollments', 'progress_percentage')) {
                    $table->decimal('progress_percentage', 5, 2)->default(0.00)->after('enrollment_status');
                }
                
                // Add attendance_count column if it doesn't exist
                if (!Schema::hasColumn('activity_enrollments', 'attendance_count')) {
                    $table->integer('attendance_count')->default(0)->after('progress_percentage');
                }
                
                // Add completion_date column if it doesn't exist
                if (!Schema::hasColumn('activity_enrollments', 'completion_date')) {
                    $table->date('completion_date')->nullable()->after('attendance_count');
                }
                
                // Add completion_notes column if it doesn't exist
                if (!Schema::hasColumn('activity_enrollments', 'completion_notes')) {
                    $table->text('completion_notes')->nullable()->after('completion_date');
                }
                
                // Add enrolled_by column if it doesn't exist
                if (!Schema::hasColumn('activity_enrollments', 'enrolled_by')) {
                    $table->unsignedBigInteger('enrolled_by')->nullable()->after('completion_notes');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop columns as they might have important data
        // Just comment out the columns we added
        if (Schema::hasTable('activity_enrollments')) {
            Schema::table('activity_enrollments', function (Blueprint $table) {
                if (Schema::hasColumn('activity_enrollments', 'progress_percentage')) {
                    $table->dropColumn('progress_percentage');
                }
                if (Schema::hasColumn('activity_enrollments', 'attendance_count')) {
                    $table->dropColumn('attendance_count');
                }
                if (Schema::hasColumn('activity_enrollments', 'completion_date')) {
                    $table->dropColumn('completion_date');
                }
                if (Schema::hasColumn('activity_enrollments', 'completion_notes')) {
                    $table->dropColumn('completion_notes');
                }
                if (Schema::hasColumn('activity_enrollments', 'enrolled_by')) {
                    $table->dropColumn('enrolled_by');
                }
            });
        }
    }
};