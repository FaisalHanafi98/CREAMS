<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Fix database schema mismatches
     * This migration fixes column name mismatches between database and code expectations
     */
    public function up(): void
    {
        // Fix activity_sessions table - add missing columns that code expects
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Add status column (copy of session_status)
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled')->after('session_status');
            
            // Add teacher_id column (copy of instructor_id)
            $table->unsignedBigInteger('teacher_id')->after('instructor_id');
            
            // Add missing columns that queries expect
            $table->string('room_number')->nullable()->after('venue');
            $table->integer('current_participants')->default(0)->after('current_enrollment');
            $table->integer('max_participants')->default(20)->after('max_capacity');
        });

        // Update the new columns with data from existing columns
        DB::statement('UPDATE activity_sessions SET status = session_status, teacher_id = instructor_id, current_participants = current_enrollment, max_participants = max_capacity');

        // Fix staff_attendances table - add user_id column to match model expectations
        Schema::table('staff_attendances', function (Blueprint $table) {
            // Add user_id column (copy of staff_id)
            $table->unsignedBigInteger('user_id')->after('staff_id');
            
            // Add missing columns that StaffAttendance model expects
            $table->time('attendance_time')->nullable()->after('time_in');
            $table->date('attendance_date')->after('date');
            $table->string('attendance_type')->default('check_in')->after('status');
            $table->unsignedBigInteger('marked_by_user_id')->nullable()->after('recorded_by');
            $table->string('marked_by_email')->nullable()->after('marked_by_user_id');
        });

        // Update the new columns with data from existing columns
        DB::statement('UPDATE staff_attendances SET user_id = staff_id, attendance_date = date, attendance_time = time_in, marked_by_user_id = recorded_by');

        // Create asset_maintenance table that the code expects
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->string('maintenance_type')->default('scheduled'); // scheduled, emergency, preventive
            $table->text('description');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable(); // staff member
            $table->unsignedBigInteger('created_by');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['asset_id']);
            $table->index(['scheduled_date']);
            $table->index(['status']);
            $table->index(['priority']);
            $table->index(['centre_id']);
            $table->index(['assigned_to']);
            $table->index(['created_by']);
        });

        // Create asset_maintenance_history table for tracking changes
        Schema::create('asset_maintenance_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('maintenance_id');
            $table->string('action'); // created, updated, status_changed, completed
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('performed_by');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['maintenance_id']);
            $table->index(['action']);
            $table->index(['performed_by']);
            $table->index(['centre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop added columns from activity_sessions
        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->dropColumn(['status', 'teacher_id', 'room_number', 'current_participants', 'max_participants']);
        });

        // Drop added columns from staff_attendances
        Schema::table('staff_attendances', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'attendance_time', 'attendance_date', 'attendance_type', 'marked_by_user_id', 'marked_by_email']);
        });

        // Drop asset maintenance tables
        Schema::dropIfExists('asset_maintenance_history');
        Schema::dropIfExists('asset_maintenance');
    }
};