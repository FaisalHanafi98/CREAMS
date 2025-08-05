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
        Schema::table('session_enrollments', function (Blueprint $table) {
            // Add attendance tracking columns if they don't exist
            if (!Schema::hasColumn('session_enrollments', 'attendance_status')) {
                $table->enum('attendance_status', ['present', 'late', 'absent', 'excused'])
                      ->nullable()
                      ->after('enrollment_status');
            }
            
            if (!Schema::hasColumn('session_enrollments', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('attendance_status');
            }
            
            if (!Schema::hasColumn('session_enrollments', 'participation_score')) {
                $table->integer('participation_score')->nullable()->after('checked_in_at');
            }
            
            if (!Schema::hasColumn('session_enrollments', 'progress_notes')) {
                $table->text('progress_notes')->nullable()->after('participation_score');
            }
            
            if (!Schema::hasColumn('session_enrollments', 'skills_demonstrated')) {
                $table->json('skills_demonstrated')->nullable()->after('progress_notes');
            }
            
            if (!Schema::hasColumn('session_enrollments', 'requires_assistance')) {
                $table->boolean('requires_assistance')->default(false)->after('skills_demonstrated');
            }
            
            if (!Schema::hasColumn('session_enrollments', 'special_requirements')) {
                $table->text('special_requirements')->nullable()->after('requires_assistance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_enrollments', function (Blueprint $table) {
            $columnsToCheck = [
                'attendance_status',
                'checked_in_at', 
                'participation_score',
                'progress_notes',
                'skills_demonstrated',
                'requires_assistance',
                'special_requirements'
            ];
            
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('session_enrollments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
