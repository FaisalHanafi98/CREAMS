<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds foreign key constraints to ensure referential integrity across critical tables.
     * This prevents orphaned records and enforces data consistency at the database level.
     */
    public function up(): void
    {
        // ACTIVITIES TABLE
        Schema::table('activities', function (Blueprint $table) {
            // Centre foreign key - RESTRICT prevents deletion of centres with activities
            if (!$this->foreignKeyExists('activities', 'activities_centre_id_foreign')) {
                $table->foreign('centre_id', 'activities_centre_id_foreign')
                      ->references('centre_id')
                      ->on('centres')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            }

            // Instructor foreign key - SET NULL allows instructor deletion
            if (!$this->foreignKeyExists('activities', 'activities_instructor_id_foreign')) {
                $table->foreign('instructor_id', 'activities_instructor_id_foreign')
                      ->references('id')
                      ->on('staffs')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
        });

        // ACTIVITY_SESSIONS TABLE
        Schema::table('activity_occurrences', function (Blueprint $table) {
            // Activity foreign key - CASCADE deletes sessions when activity is deleted
            if (!$this->foreignKeyExists('activity_occurrences', 'activity_occurrences_activity_id_foreign')) {
                $table->foreign('activity_id', 'activity_occurrences_activity_id_foreign')
                      ->references('id')
                      ->on('activities')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }

            // Instructor foreign key - SET NULL allows instructor deletion
            if (!$this->foreignKeyExists('activity_occurrences', 'activity_occurrences_instructor_id_foreign')) {
                $table->foreign('instructor_id', 'activity_occurrences_instructor_id_foreign')
                      ->references('id')
                      ->on('staffs')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
        });

        // ACTIVITY_ENROLLMENTS TABLE
        Schema::table('activity_enrollments', function (Blueprint $table) {
            // Activity foreign key - CASCADE deletes enrollments when activity deleted
            if (!$this->foreignKeyExists('activity_enrollments', 'activity_enrollments_activity_id_foreign')) {
                $table->foreign('activity_id', 'activity_enrollments_activity_id_foreign')
                      ->references('id')
                      ->on('activities')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }

            // Trainee foreign key - CASCADE deletes enrollments when trainee deleted
            if (!$this->foreignKeyExists('activity_enrollments', 'activity_enrollments_trainee_id_foreign')) {
                $table->foreign('trainee_id', 'activity_enrollments_trainee_id_foreign')
                      ->references('id')
                      ->on('trainees')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            }
        });

        // TRAINEES TABLE
        Schema::table('trainees', function (Blueprint $table) {
            // Centre foreign key - RESTRICT prevents deletion of centres with trainees
            if (!$this->foreignKeyExists('trainees', 'trainees_centre_id_foreign')) {
                $table->foreign('centre_id', 'trainees_centre_id_foreign')
                      ->references('centre_id')
                      ->on('centres')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            }
        });

        // STAFFS TABLE (renamed from users)
        Schema::table('staffs', function (Blueprint $table) {
            // Centre foreign key - RESTRICT prevents deletion of centres with staff
            if (!$this->foreignKeyExists('staffs', 'staffs_centre_id_foreign')) {
                $table->foreign('centre_id', 'staffs_centre_id_foreign')
                      ->references('centre_id')
                      ->on('centres')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            }
        });

        // VOLUNTEERS TABLE
        Schema::table('volunteers', function (Blueprint $table) {
            // Centre foreign key - RESTRICT prevents deletion of centres with volunteers
            if (!$this->foreignKeyExists('volunteers', 'volunteers_centre_id_foreign')) {
                $table->foreign('centre_id', 'volunteers_centre_id_foreign')
                      ->references('centre_id')
                      ->on('centres')
                      ->onDelete('restrict')
                      ->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign('activities_centre_id_foreign');
            $table->dropForeign('activities_instructor_id_foreign');
        });

        Schema::table('activity_occurrences', function (Blueprint $table) {
            $table->dropForeign('activity_occurrences_activity_id_foreign');
            $table->dropForeign('activity_occurrences_instructor_id_foreign');
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropForeign('activity_enrollments_activity_id_foreign');
            $table->dropForeign('activity_enrollments_trainee_id_foreign');
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->dropForeign('trainees_centre_id_foreign');
        });

        Schema::table('staffs', function (Blueprint $table) {
            $table->dropForeign('staffs_centre_id_foreign');
        });

        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropForeign('volunteers_centre_id_foreign');
        });
    }

    /**
     * Check if a foreign key constraint already exists
     */
    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $databaseName = DB::connection()->getDatabaseName();

        $exists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$databaseName, $table, $foreignKey]);

        return !empty($exists);
    }
};
