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
        // User foreign keys
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });

        // Course foreign keys
        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
        });

        // Trainee foreign keys
        Schema::table('trainees', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
        });

        // Activity foreign keys
        Schema::table('activities', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
        });

        // Activity Sessions foreign keys
        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
        });

        // Session Enrollments foreign keys
        Schema::table('session_enrollments', function (Blueprint $table) {
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
        });

        // Activity Enrollments foreign keys
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
        });

        // Activity Schedules foreign keys
        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
        });

        // Asset foreign keys
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('assigned_to_id')->references('id')->on('users')->onDelete('set null');
        });

        // Message foreign keys
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Classes foreign keys
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
        });

        // Event foreign keys
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
        });

        // Attendance foreign keys
        Schema::table('attendance', function (Blueprint $table) {
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
        });

        // Letter foreign keys
        Schema::table('letters', function (Blueprint $table) {
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys in reverse order
        Schema::table('letters', function (Blueprint $table) {
            $table->dropForeign(['trainee_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['centre_id']);
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['trainee_id']);
            $table->dropForeign(['session_id']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->dropForeign(['centre_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['centre_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['recipient_id']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to_id']);
        });

        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropForeign(['trainee_id']);
        });

        Schema::table('session_enrollments', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropForeign(['trainee_id']);
        });

        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['centre_id']);
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
            $table->dropForeign(['course_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['centre_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });
    }
};