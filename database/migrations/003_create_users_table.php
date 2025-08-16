<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - User Management Module
     * Module: User Management (Staff & Roles)
     * Priority: 003 - Critical (Authentication & Authorization Base)
     */
    public function up(): void
    {
        // Main users table - All staff members
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('iium_id')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('position')->nullable();
            $table->string('education_level')->nullable();
            $table->string('education_specialization')->nullable();
            $table->string('teaching_specialization')->nullable();
            $table->string('avatar')->nullable();
            $table->text('about')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('role', ['admin', 'supervisor', 'teacher', 'ajk'])->default('teacher');
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('pending');
            $table->string('centre_id')->nullable();
            $table->string('centre_location')->nullable();
            $table->timestamp('user_last_accessed_at')->nullable();
            $table->text('review')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['role', 'status']);
            $table->index(['centre_id']);
            $table->index(['iium_id']);
            $table->index(['email']);
        });

        // Staff attendance tracking
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'sick_leave', 'annual_leave'])->default('present');
            $table->text('notes')->nullable();
            $table->string('centre_id');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'attendance_date']);
            $table->index(['centre_id']);
            $table->index(['attendance_date']);
            $table->index(['status']);
        });

        // Staff attendances (alternative table structure)
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])->default('present');
            $table->text('remarks')->nullable();
            $table->string('centre_id');
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
            
            $table->index(['staff_id', 'date']);
            $table->index(['centre_id']);
            $table->index(['date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
        Schema::dropIfExists('staff_attendance');
        Schema::dropIfExists('users');
    }
};