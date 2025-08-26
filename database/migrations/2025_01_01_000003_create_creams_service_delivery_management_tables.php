<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreamsServiceDeliveryManagementTables extends Migration
{
    /**
     * CREAMS Service Delivery Management Migration
     * Tables: activity_categories, activities, activity_sessions, activity_enrollments
     * Dependencies: Foundation Management, Client Management
     */
    public function up(): void
    {
        // Skip if tables already exist (preserves current logic)
        if (Schema::hasTable('activity_categories')) {
            return;
        }

        // 1. ACTIVITY CATEGORIES - Service classification (preserves current structure)
        Schema::create('activity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->text('category_description')->nullable();
            $table->string('category_type', 50)->nullable();
            $table->string('category_color', 7)->default('#007bff');
            $table->string('category_icon', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_type', 'is_active']);
        });

        // 2. ACTIVITIES - Service programs (preserves current structure)
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
            $table->string('activity_location')->nullable();
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('times_conducted')->default(0);
            $table->timestamps();
            
            $table->index(['category_id', 'centre_id', 'is_active']);
            $table->index('instructor_id');
        });

        // 3. ACTIVITY SESSIONS - Individual service instances (preserves current structure)
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
            
            $table->index(['activity_id', 'session_date', 'session_status']);
            $table->index('instructor_id');
        });

        // 4. ACTIVITY ENROLLMENTS - Service assignments (preserves current structure)
        Schema::create('activity_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('trainee_id');
            $table->date('enrollment_date');
            $table->enum('enrollment_status', ['enrolled', 'completed', 'dropped', 'pending'])->default('enrolled');
            $table->text('enrollment_notes')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->integer('attendance_count')->default(0);
            $table->date('completion_date')->nullable();
            $table->text('completion_notes')->nullable();
            $table->integer('enrolled_by')->nullable();
            $table->timestamps();
            
            $table->index(['activity_id', 'trainee_id', 'enrollment_status'], 'activity_enrollments_composite_idx');
            $table->index('enrollment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_enrollments');
        Schema::dropIfExists('activity_sessions');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('activity_categories');
    }
}