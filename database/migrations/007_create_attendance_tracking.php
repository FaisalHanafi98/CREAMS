<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Attendance & Progress Tracking Module
     * Module: Attendance & Progress Tracking
     * Priority: 007 - High (Progress Monitoring)
     */
    public function up(): void
    {
        // General attendance table (legacy compatibility)
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('attendance_status', ['present', 'absent', 'late', 'excused', 'partial'])->default('present');
            $table->text('attendance_notes')->nullable();
            $table->integer('participation_level')->nullable(); // 1-5 scale
            $table->text('progress_observation')->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['trainee_id']);
            $table->index(['activity_id']);
            $table->index(['session_id']);
            $table->index(['class_id']);
            $table->index(['attendance_date']);
            $table->index(['attendance_status']);
            $table->index(['centre_id']);
        });

        // Detailed attendances table (enhanced tracking)
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'sick'])->default('present');
            $table->text('notes')->nullable();
            $table->integer('mood_rating')->nullable(); // 1-10 scale
            $table->integer('engagement_level')->nullable(); // 1-10 scale
            $table->text('achievements')->nullable();
            $table->text('challenges')->nullable();
            $table->json('goals_progress')->nullable(); // JSON tracking of individual goals
            $table->unsignedBigInteger('recorded_by');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['trainee_id', 'date']);
            $table->index(['activity_id']);
            $table->index(['session_id']);
            $table->index(['status']);
            $table->index(['centre_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance');
    }
};