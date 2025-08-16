<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Activity System Module
     * Module: Activity System (Core Activities)
     * Priority: 005 - High (Core Rehabilitation Functions)
     */
    public function up(): void
    {
        // Categories table for activity classification
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->text('category_description')->nullable();
            $table->string('category_icon')->nullable();
            $table->string('category_color')->nullable();
            $table->enum('category_type', ['rehabilitation', 'academic', 'recreational', 'faith'])->default('rehabilitation');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category_type']);
            $table->index(['is_active']);
        });

        // Main activities table
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id')->unique();
            $table->string('activity_name');
            $table->text('activity_description');
            $table->string('activity_type');
            $table->date('activity_date');
            $table->time('activity_start_time');
            $table->time('activity_end_time');
            $table->string('activity_location');
            $table->integer('max_participants')->default(20);
            $table->integer('current_participants')->default(0);
            $table->text('activity_goals')->nullable();
            $table->text('activity_outcomes')->nullable();
            $table->string('activity_image')->nullable();
            $table->json('required_resources')->nullable();
            $table->enum('activity_status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->string('centre_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('instructor_id')->nullable();
            
            // Enhanced activity tracking
            $table->integer('times_conducted')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('min_participants')->default(1);
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('age_group', ['children', 'adolescents', 'adults', 'elderly', 'all_ages'])->default('all_ages');
            $table->string('activity_period')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['activity_id']);
            $table->index(['centre_id']);
            $table->index(['activity_status']);
            $table->index(['activity_date']);
            $table->index(['category_id']);
            $table->index(['instructor_id']);
            $table->index(['difficulty_level']);
            $table->index(['is_active']);
        });

        // Activity schedules for recurring activities
        Schema::create('activity_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom'])->default('weekly');
            $table->json('days_of_week')->nullable(); // [1,2,3] for Mon,Tue,Wed
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('venue');
            $table->integer('max_capacity')->default(20);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['activity_id']);
            $table->index(['frequency']);
            $table->index(['start_date', 'end_date']);
            $table->index(['is_active']);
        });

        // Classes table for structured learning
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_name');
            $table->text('class_description')->nullable();
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('class_schedule')->nullable();
            $table->integer('max_students')->default(25);
            $table->integer('current_enrollment')->default(0);
            $table->enum('class_status', ['active', 'inactive', 'completed'])->default('active');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['instructor_id']);
            $table->index(['course_id']);
            $table->index(['centre_id']);
            $table->index(['class_status']);
        });

        // Events table for special activities
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->text('event_description')->nullable();
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('event_location');
            $table->integer('max_attendees')->nullable();
            $table->enum('event_type', ['workshop', 'seminar', 'celebration', 'meeting', 'other'])->default('other');
            $table->enum('event_status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->unsignedBigInteger('organizer_id');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['event_date']);
            $table->index(['event_status']);
            $table->index(['organizer_id']);
            $table->index(['centre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('activity_schedules');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('categories');
    }
};