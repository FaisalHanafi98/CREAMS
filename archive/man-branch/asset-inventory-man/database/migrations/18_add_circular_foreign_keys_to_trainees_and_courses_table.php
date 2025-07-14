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
        // Drop existing foreign key if it exists
        try {
            // MySQL specific approach to drop foreign key
            DB::statement('ALTER TABLE trainees DROP FOREIGN KEY IF EXISTS trainees_course_id_foreign');
        } catch (\Exception $e) {
            // Silently catch if key doesn't exist
        }

        // Ensure courses table exists before adding constraint
        if (!Schema::hasTable('courses')) {
            throw new \Exception('Courses table must exist before adding foreign key');
        }

        // Attempt to add foreign key with error handling
        try {
            Schema::table('trainees', function (Blueprint $table) {
                // Drop existing foreign key constraint if it exists
                $table->dropForeign(['course_id']);
            });
        } catch (\Exception $e) {
            // Silently catch if foreign key doesn't exist
        }

        // Re-add foreign key constraint
        Schema::table('trainees', function (Blueprint $table) {
            $table->foreign('course_id')
                  ->references('course_id')
                  ->on('courses')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainees', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['course_id']);
        });
    }
};