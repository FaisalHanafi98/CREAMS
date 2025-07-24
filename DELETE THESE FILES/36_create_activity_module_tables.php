<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    public function up(): void
    {
        // 1. Activities Table
        if (!Schema::hasTable('activities')) {
            Log::info('Creating activities table');
            Schema::create('activities', function (Blueprint $table) {
                $table->id();
                $table->string('activity_code', 50)->unique();
                $table->string('activity_name');
                $table->string('category');
                $table->text('description')->nullable();
                $table->text('objectives')->nullable();
                $table->text('materials_needed')->nullable();
                $table->string('age_group', 50)->nullable();
                $table->enum('difficulty_level', ['Beginner', 'Intermediate', 'Advanced'])->default('Beginner');
                $table->integer('max_class_size')->default(10);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();

                $table->index(['category', 'is_active'], 'idx_category_active');
                $table->index('activity_code', 'idx_code');
            });
        } else {
            Log::info('Activities table already exists, skipping creation');
        }

        // 2. Activity Sessions Table
        if (!Schema::hasTable('activity_sessions')) {
            Log::info('Creating activity_sessions table');
            Schema::create('activity_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('activities')->onDelete('cascade');
                $table->foreignId('teacher_id')->constrained('users');
                $table->string('class_name', 100);
                $table->string('academic_year', 20);
                $table->string('semester', 20);
                $table->enum('day_of_week', ['Monday','Tuesday','Wednesday','Thursday','Friday']);
                $table->time('start_time');
                $table->time('end_time');
                $table->string('location', 100)->nullable();
                $table->integer('current_enrollment')->default(0);
                $table->integer('max_capacity')->default(10);
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['teacher_id', 'day_of_week', 'start_time'], 'idx_teacher_schedule');
                $table->index(['is_active', 'academic_year', 'semester'], 'idx_active_sessions');
                $table->unique(['activity_id', 'class_name', 'day_of_week', 'academic_year', 'semester'], 'unique_schedule');
            });
        } else {
            Log::info('Activity_sessions table already exists, skipping creation');
        }

        // 3. Session Enrollments Table
        if (!Schema::hasTable('session_enrollments')) {
            Log::info('Creating session_enrollments table');
            Schema::create('session_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('activity_sessions')->onDelete('cascade');
                $table->foreignId('trainee_id')->constrained('trainees')->onDelete('cascade');
                $table->datetime('enrollment_date');
                $table->enum('status', ['active', 'withdrawn', 'completed', 'waitlisted'])->default('active');
                $table->foreignId('enrolled_by')->constrained('users');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['session_id', 'status']);
                $table->index(['trainee_id', 'status']);
                $table->unique(['session_id', 'trainee_id']);
            });
        } else {
            Log::info('Session_enrollments table already exists, skipping creation');
        }

        // 4. Activity Attendance Table
        if (!Schema::hasTable('activity_attendance')) {
            Log::info('Creating activity_attendance table');
            Schema::create('activity_attendance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('activity_sessions')->onDelete('cascade');
                $table->foreignId('trainee_id')->constrained('trainees')->onDelete('cascade');
                $table->date('attendance_date');
                $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
                $table->time('arrival_time')->nullable();
                $table->time('departure_time')->nullable();
                $table->integer('participation_score')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('marked_by')->nullable()->constrained('users');
                $table->timestamps();

                $table->index(['session_id', 'attendance_date']);
                $table->index(['trainee_id', 'attendance_date']);
                $table->index(['attendance_date', 'status']);
                $table->unique(['session_id', 'trainee_id', 'attendance_date'], 'unique_attendance_record');
            });
        } else {
            Log::info('Activity_attendance table already exists, skipping creation');
        }
    }

    public function down(): void
    {
        // Drop tables in reverse order to avoid foreign key constraint issues
        Schema::dropIfExists('activity_attendance');
        Schema::dropIfExists('session_enrollments');
        Schema::dropIfExists('activity_sessions');
        Schema::dropIfExists('activities');
    }
};