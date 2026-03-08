<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds performance indexes to critical tables for faster query execution.
     * Includes composite indexes for common query patterns and unique constraints
     * to prevent data duplication.
     */
    public function up(): void
    {
        // ACTIVITIES TABLE - Index for filtering and searching
        Schema::table('activities', function (Blueprint $table) {
            $table->index('centre_id', 'idx_activities_centre');
            $table->index('instructor_id', 'idx_activities_instructor');
            $table->index('category', 'idx_activities_category');
            $table->index('is_active', 'idx_activities_is_active');
            // Composite index for common query: active activities per centre
            $table->index(['is_active', 'centre_id'], 'idx_activities_active_centre');
        });

        // TRAINEES TABLE - Index for searching and filtering
        Schema::table('trainees', function (Blueprint $table) {
            $table->index('centre_id', 'idx_trainees_centre');
            $table->index('trainee_condition', 'idx_trainees_condition');
            $table->index('status', 'idx_trainees_status');
            // Composite index for common query: active trainees per centre
            $table->index(['status', 'centre_id'], 'idx_trainees_status_centre');
            // Unique constraint on IC number (Malaysian National ID)
            $table->unique('ic_number', 'uniq_trainees_ic_number');
        });

        // ACTIVITY_SESSIONS TABLE - Index for scheduling queries
        Schema::table('activity_occurrences', function (Blueprint $table) {
            $table->index('activity_id', 'idx_sessions_activity');
            $table->index('session_date', 'idx_sessions_date');
            $table->index('session_status', 'idx_sessions_status');
            $table->index('instructor_id', 'idx_sessions_instructor');
            // Composite index for instructor schedule lookups
            $table->index(['instructor_id', 'session_date'], 'idx_sessions_instructor_date');
            // Composite index for activity timeline
            $table->index(['activity_id', 'session_date'], 'idx_sessions_activity_date');
        });

        // ACTIVITY_ENROLLMENTS TABLE - Index for enrollment tracking
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->index('activity_id', 'idx_enrollments_activity');
            $table->index('trainee_id', 'idx_enrollments_trainee');
            $table->index('enrollment_status', 'idx_enrollments_status');
            // Unique constraint to prevent duplicate enrollments
            $table->unique(['activity_id', 'trainee_id'], 'uniq_enrollments_activity_trainee');
        });

        // USERS TABLE - Index for staff lookups
        Schema::table('staffs', function (Blueprint $table) {
            $table->index('centre_id', 'idx_users_centre');
            $table->index('role', 'idx_users_role');
            $table->index('status', 'idx_users_status');
            // Composite index for staff queries per centre
            $table->index(['centre_id', 'role'], 'idx_users_centre_role');
        });

        // VOLUNTEERS TABLE - Index for volunteer management
        Schema::table('volunteers', function (Blueprint $table) {
            $table->index('centre_id', 'idx_volunteers_centre');
            $table->index('status', 'idx_volunteers_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('idx_activities_centre');
            $table->dropIndex('idx_activities_instructor');
            $table->dropIndex('idx_activities_category');
            $table->dropIndex('idx_activities_is_active');
            $table->dropIndex('idx_activities_active_centre');
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->dropIndex('idx_trainees_centre');
            $table->dropIndex('idx_trainees_condition');
            $table->dropIndex('idx_trainees_status');
            $table->dropIndex('idx_trainees_status_centre');
            $table->dropUnique('uniq_trainees_ic_number');
        });

        Schema::table('activity_occurrences', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_activity');
            $table->dropIndex('idx_sessions_date');
            $table->dropIndex('idx_sessions_status');
            $table->dropIndex('idx_sessions_instructor');
            $table->dropIndex('idx_sessions_instructor_date');
            $table->dropIndex('idx_sessions_activity_date');
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropIndex('idx_enrollments_activity');
            $table->dropIndex('idx_enrollments_trainee');
            $table->dropIndex('idx_enrollments_status');
            $table->dropUnique('uniq_enrollments_activity_trainee');
        });

        Schema::table('staffs', function (Blueprint $table) {
            $table->dropIndex('idx_users_centre');
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_centre_role');
        });

        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropIndex('idx_volunteers_centre');
            $table->dropIndex('idx_volunteers_status');
        });
    }
};
