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
        // Add foreign key constraints

        // Users table foreign keys
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Courses table foreign keys
        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Trainees table foreign keys
        Schema::table('trainees', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
        });

        // Activities table foreign keys
        Schema::table('activities', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('set null');
        });

        // Activity sessions table foreign keys
        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Activity enrollments table foreign keys
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('enrolled_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Session enrollments table foreign keys
        Schema::table('session_enrollments', function (Blueprint $table) {
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('enrolled_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Activity schedules table foreign keys
        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Assets table foreign keys
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('asset_type_id')->references('id')->on('asset_types')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });

        // Notifications table foreign keys
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Messages table foreign keys
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Contact messages table foreign keys
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->foreign('replied_by')->references('id')->on('users')->onDelete('set null');
        });

        // Classes table foreign keys
        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('set null');
        });

        // Events table foreign keys
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Attendance table foreign keys
        Schema::table('attendance', function (Blueprint $table) {
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Letter templates table foreign keys
        Schema::table('letter_templates', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Letters table foreign keys
        Schema::table('letters', function (Blueprint $table) {
            $table->foreign('template_id')->references('id')->on('letter_templates')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints in reverse order
        Schema::table('letters', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('letter_templates', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropForeign(['trainee_id']);
            $table->dropForeign(['activity_id']);
            $table->dropForeign(['session_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['recorded_by']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->dropForeign(['course_id']);
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropForeign(['replied_by']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['recipient_id']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['asset_type_id']);
            $table->dropForeign(['assigned_to']);
        });

        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropForeign(['created_by']);
        });

        Schema::table('session_enrollments', function (Blueprint $table) {
            $table->dropForeign(['session_id']);
            $table->dropForeign(['trainee_id']);
            $table->dropForeign(['enrolled_by']);
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropForeign(['trainee_id']);
            $table->dropForeign(['enrolled_by']);
        });

        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropForeign(['instructor_id']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['instructor_id']);
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
        });
    }
};