<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreamsAttendanceManagementTables extends Migration
{
    /**
     * CREAMS Attendance Management Migration
     * Tables: staff_attendances, trainee_attendances, session_attendance, attendance_alerts
     * Dependencies: Foundation Management, Client Management, Service Delivery Management
     */
    public function up(): void
    {
        // Skip if tables already exist (preserves current logic)
        if (Schema::hasTable('staff_attendances')) {
            return;
        }

        // 1. STAFF ATTENDANCES - Staff tracking (preserves current structure)
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('centre_id', 10)->nullable();
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            // Removed check_out_time - staff only need to check in
            $table->enum('status', ['present', 'absent', 'late', 'leave'])->default('absent');
            $table->string('leave_type', 50)->nullable(); // mandatory when status = 'leave'
            // Removed notes - keeping only remarks
            $table->boolean('approved')->default(false);
            $table->integer('approved_by')->nullable(); // mandatory for leave status
            $table->timestamp('approved_at')->nullable();
            $table->integer('marked_by_user_id')->nullable(); // must be filled if marked_by_email is filled
            $table->string('marked_by_email')->nullable(); // must be filled if marked_by_user_id is filled
            // Removed attendance_time - using check_in_time only
            $table->text('remarks')->nullable();
            // Removed total_hours - not needed for check-in only system
            $table->timestamps();
            
            $table->index(['user_id', 'attendance_date']);
            $table->index(['centre_id', 'attendance_date', 'status']);
        });

        // 2. TRAINEE ATTENDANCES - Service attendance (preserves current structure)
        Schema::create('trainee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('marked_by_user_id')->nullable();
            $table->timestamp('marked_at')->nullable();
            $table->timestamps();
            
            $table->index(['trainee_id', 'attendance_date']);
            $table->index(['activity_id', 'session_id']);
            $table->index(['attendance_date', 'status']);
        });

        // 3. SESSION ATTENDANCE - Detailed session tracking (preserves current structure)
        Schema::create('session_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->enum('attendance_status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'trainee_id']);
            $table->index('attendance_status');
        });

        // 4. ATTENDANCE ALERTS - Monitoring system (preserves current structure)
        Schema::create('attendance_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('alert_type', ['staff', 'trainee']);
            $table->integer('user_id')->nullable();
            $table->integer('trainee_id')->nullable();
            $table->string('alert_message');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->integer('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            $table->index(['alert_type', 'severity', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_alerts');
        Schema::dropIfExists('session_attendance');
        Schema::dropIfExists('trainee_attendances');
        Schema::dropIfExists('staff_attendances');
    }
}