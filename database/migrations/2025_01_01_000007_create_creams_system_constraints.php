<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreamsSystemConstraints extends Migration
{
    /**
     * CREAMS System Constraints Migration
     * Purpose: Add all foreign key relationships and performance indexes
     * Dependencies: All previous modules
     */
    public function up(): void
    {
        // Skip if constraints already exist (preserves current logic)
        if (Schema::hasColumn('users', 'centre_id') && 
            collect(DB::select("SHOW CREATE TABLE users"))[0]->{'Create Table'} ?? '' && 
            str_contains(collect(DB::select("SHOW CREATE TABLE users"))[0]->{'Create Table'} ?? '', 'FOREIGN KEY')) {
            return;
        }

        // 1. FOUNDATION CONSTRAINTS
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });

        // 2. CLIENT MANAGEMENT CONSTRAINTS  
        Schema::table('trainees', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });

        Schema::table('volunteers', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
            $table->foreign('replied_by')->references('id')->on('users')->onDelete('set null');
        });

        // 3. SERVICE DELIVERY CONSTRAINTS
        Schema::table('activities', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('activity_categories')->onDelete('cascade');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
        });

        // 4. ATTENDANCE CONSTRAINTS
        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
            $table->foreign('marked_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('trainee_attendances', function (Blueprint $table) {
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('set null');
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('set null');
            $table->foreign('marked_by_user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('session_attendance', function (Blueprint $table) {
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('attendance_alerts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
        });

        // 5. ASSET MANAGEMENT CONSTRAINTS
        Schema::table('asset_types', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('cascade');
        });

        Schema::table('asset_locations', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('asset_types')->onDelete('set null');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('asset_locations')->onDelete('set null');
            $table->foreign('assigned_to_user')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('asset_maintenance', function (Blueprint $table) {
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });

        Schema::table('asset_maintenance_history', function (Blueprint $table) {
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('maintenance_id')->references('id')->on('asset_maintenance')->onDelete('set null');
        });

        Schema::table('asset_movements', function (Blueprint $table) {
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('from_location_id')->references('id')->on('asset_locations')->onDelete('set null');
            $table->foreign('to_location_id')->references('id')->on('asset_locations')->onDelete('cascade');
            $table->foreign('moved_by_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 6. COMMUNICATION CONSTRAINTS
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('message_recipients', function (Blueprint $table) {
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('letters', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('cascade');
        });

        Schema::table('letter_templates', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys in reverse dependency order
        Schema::table('letter_templates', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('letters', function (Blueprint $table) {
            $table->dropForeign(['created_by', 'centre_id']);
        });

        Schema::table('message_recipients', function (Blueprint $table) {
            $table->dropForeign(['message_id', 'recipient_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
        });

        Schema::table('asset_movements', function (Blueprint $table) {
            $table->dropForeign(['asset_id', 'from_location_id', 'to_location_id', 'moved_by_user_id']);
        });

        Schema::table('asset_maintenance_history', function (Blueprint $table) {
            $table->dropForeign(['asset_id', 'maintenance_id']);
        });

        Schema::table('asset_maintenance', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['category_id', 'type_id', 'centre_id', 'location_id', 'assigned_to_user']);
        });

        Schema::table('asset_locations', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });

        Schema::table('asset_types', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('attendance_alerts', function (Blueprint $table) {
            $table->dropForeign(['user_id', 'trainee_id', 'resolved_by']);
        });

        Schema::table('session_attendance', function (Blueprint $table) {
            $table->dropForeign(['session_id', 'trainee_id', 'marked_by']);
        });

        Schema::table('trainee_attendances', function (Blueprint $table) {
            $table->dropForeign(['trainee_id', 'activity_id', 'session_id', 'marked_by_user_id']);
        });

        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dropForeign(['user_id', 'centre_id', 'marked_by_user_id', 'approved_by']);
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropForeign(['activity_id', 'trainee_id']);
        });

        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->dropForeign(['activity_id', 'instructor_id']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropForeign(['category_id', 'centre_id', 'instructor_id']);
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropForeign(['centre_id', 'replied_by']);
        });

        Schema::table('volunteers', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });

        Schema::table('trainees', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });
    }
}