<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create all remaining tables for CREAMS system
     */
    public function up(): void
    {
        // Skip if tables already exist (for existing installations)
        if (Schema::hasTable('trainees')) {
            return;
        }

        // 1. TRAINEES TABLE
        Schema::create('trainees', function (Blueprint $table) {
            $table->id();
            $table->string('trainee_id', 50)->unique();
            $table->string('trainee_first_name', 100);
            $table->string('trainee_last_name', 100);
            $table->string('trainee_email')->unique();
            $table->string('ic_number', 15)->unique();
            $table->date('trainee_date_of_birth');
            $table->enum('gender', ['Male', 'Female']);
            $table->string('trainee_phone_number', 20)->nullable();
            $table->text('trainee_address')->nullable();
            $table->string('trainee_condition')->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->string('centre_name')->nullable();
            $table->foreignId('course_id')->nullable();
            $table->string('guardian_name', 100);
            $table->string('guardian_phone', 20);
            $table->string('guardian_email', 100)->nullable();
            $table->text('guardian_address');
            $table->string('emergency_contact_name', 100);
            $table->string('emergency_contact_phone', 20);
            $table->enum('enrollment_status', ['active', 'inactive', 'graduated', 'transferred', 'withdrawn'])->default('active');
            $table->date('enrollment_date')->default(now());
            $table->date('graduation_date')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medications')->nullable();
            $table->text('special_needs')->nullable();
            $table->text('behavioral_notes')->nullable();
            $table->boolean('consent_photo')->default(false);
            $table->boolean('consent_video')->default(false);
            $table->boolean('consent_research')->default(false);
            $table->string('profile_picture')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('centre_id');
            $table->index('enrollment_status');
            $table->index('trainee_condition');
        });

        // 2. ACTIVITY_CATEGORIES TABLE
        Schema::create('activity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->text('category_description')->nullable();
            $table->string('category_type', 50)->nullable();
            $table->string('category_color', 7)->default('#007bff');
            $table->string('category_icon', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('category_type');
            $table->index('is_active');
        });

        // 3. ACTIVITIES TABLE
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_name');
            $table->text('activity_description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('centre_id', 10);
            $table->integer('duration_weeks')->default(12);
            $table->integer('sessions_per_week')->default(2);
            $table->integer('session_duration_minutes')->default(60);
            $table->integer('max_participants')->default(10);
            $table->text('learning_outcomes')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('times_conducted')->default(0);
            $table->timestamps();
            
            $table->index('category_id');
            $table->index('centre_id');
            $table->index('instructor_id');
            $table->index('is_active');
        });

        // Continue with other essential tables...
        $this->createRemainingTables();
    }

    private function createRemainingTables()
    {
        // Create remaining tables if they don't exist
        if (!Schema::hasTable('activity_sessions')) {
            Schema::create('activity_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('session_name');
                $table->text('session_description')->nullable();
                $table->date('session_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('location')->nullable();
                $table->unsignedBigInteger('instructor_id')->nullable();
                $table->enum('session_status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
                $table->text('session_notes')->nullable();
                $table->integer('max_participants')->nullable();
                $table->timestamps();
                
                $table->index('activity_id');
                $table->index('session_date');
                $table->index('instructor_id');
                $table->index('session_status');
            });
        }

        // Add other essential tables
        $this->createAttendanceTables();
        $this->createAssetTables();
        $this->createCommunicationTables();
        $this->createLetterTables();
    }

    private function createAttendanceTables()
    {
        if (!Schema::hasTable('staff_attendances')) {
            Schema::create('staff_attendances', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('centre_id', 10);
                $table->date('attendance_date');
                $table->time('check_in_time')->nullable();
                $table->time('check_out_time')->nullable();
                $table->enum('status', ['present', 'absent', 'late', 'sick_leave', 'emergency_leave', 'authorized_leave'])->default('present');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'attendance_date']);
                $table->index('centre_id');
                $table->index('status');
            });
        }
    }

    private function createAssetTables()
    {
        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->id();
                $table->string('asset_name');
                $table->string('asset_code')->unique();
                $table->text('description')->nullable();
                $table->string('centre_id', 10);
                $table->enum('status', ['available', 'in_use', 'maintenance', 'damaged', 'disposed'])->default('available');
                $table->timestamps();
                
                $table->index('centre_id');
                $table->index('status');
            });
        }
    }

    private function createCommunicationTables()
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id');
                $table->unsignedBigInteger('receiver_id');
                $table->string('subject');
                $table->text('body');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                
                $table->index(['sender_id', 'receiver_id']);
                $table->index('is_read');
            });
        }
    }

    private function createLetterTables()
    {
        if (!Schema::hasTable('letter_templates')) {
            Schema::create('letter_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_name');
                $table->text('template_content');
                $table->string('template_type', 50)->default('general');
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system_template')->default(false);
                $table->timestamps();
                
                $table->index('template_type');
                $table->index('is_active');
            });
        }

        if (!Schema::hasTable('letters')) {
            Schema::create('letters', function (Blueprint $table) {
                $table->id();
                $table->string('letter_title');
                $table->text('letter_content');
                $table->string('recipient_name');
                $table->string('recipient_address')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->enum('status', ['draft', 'sent', 'archived'])->default('draft');
                $table->timestamps();
                
                $table->index('created_by');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('letters');
        Schema::dropIfExists('letter_templates');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('staff_attendances');
        Schema::dropIfExists('activity_sessions');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('activity_categories');
        Schema::dropIfExists('trainees');
    }
};