<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds soft delete support to critical tables. This allows "deleted" records
     * to be preserved in the database for audit trails and potential recovery.
     * Soft-deleted records are automatically excluded from queries by Laravel.
     */
    public function up(): void
    {
        // ACTIVITIES TABLE - Preserve activity history
        Schema::table('activities', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'idx_activities_deleted_at');
        });

        // TRAINEES TABLE - Preserve trainee history (critical for compliance)
        Schema::table('trainees', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'idx_trainees_deleted_at');
        });

        // ACTIVITY_ENROLLMENTS TABLE - Preserve enrollment history
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'idx_enrollments_deleted_at');
        });

        // ACTIVITY_SESSIONS TABLE - Preserve session history
        Schema::table('activity_occurrences', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'idx_sessions_deleted_at');
        });

        // USERS TABLE - Preserve staff history (for audit trail)
        Schema::table('staffs', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'idx_users_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex('idx_activities_deleted_at');
            $table->dropSoftDeletes();
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->dropIndex('idx_trainees_deleted_at');
            $table->dropSoftDeletes();
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropIndex('idx_enrollments_deleted_at');
            $table->dropSoftDeletes();
        });

        Schema::table('activity_occurrences', function (Blueprint $table) {
            $table->dropIndex('idx_sessions_deleted_at');
            $table->dropSoftDeletes();
        });

        Schema::table('staffs', function (Blueprint $table) {
            $table->dropIndex('idx_users_deleted_at');
            $table->dropSoftDeletes();
        });
    }
};
