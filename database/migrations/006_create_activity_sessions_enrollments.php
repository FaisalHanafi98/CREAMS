<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Activity Sessions & Enrollments Module
     * Module: Activity Sessions & Enrollments
     * Priority: 006 - High (Session Management & Registration)
     */
    public function up(): void
    {
        // Activity sessions - Individual session instances
        Schema::create('activity_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->date('session_date');
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('venue');
            $table->unsignedBigInteger('instructor_id');
            $table->integer('max_capacity')->default(20);
            $table->integer('current_enrollment')->default(0);
            $table->enum('session_status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->boolean('attendance_marked')->default(false);
            $table->text('session_notes')->nullable();
            $table->text('materials_used')->nullable();
            $table->decimal('session_rating', 3, 2)->nullable();
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['activity_id']);
            $table->index(['session_date']);
            $table->index(['scheduled_date']);
            $table->index(['instructor_id']);
            $table->index(['session_status']);
            $table->index(['centre_id']);
        });

        // Activity enrollments - Activity-level registration
        Schema::create('activity_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('trainee_id');
            $table->date('enrollment_date');
            $table->enum('enrollment_status', ['enrolled', 'withdrawn', 'completed', 'suspended'])->default('enrolled');
            $table->text('enrollment_notes')->nullable();
            $table->text('individual_goals')->nullable();
            $table->text('progress_notes')->nullable();
            $table->integer('sessions_attended')->default(0);
            $table->integer('total_sessions')->default(0);
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->decimal('overall_progress', 5, 2)->default(0);
            $table->unsignedBigInteger('enrolled_by');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['activity_id']);
            $table->index(['trainee_id']);
            $table->index(['enrollment_status']);
            $table->index(['enrollment_date']);
            $table->index(['centre_id']);
            $table->unique(['activity_id', 'trainee_id']);
        });

        // Session enrollments - Session-level tracking
        Schema::create('session_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->enum('enrollment_status', ['enrolled', 'waitlist', 'cancelled'])->default('enrolled');
            $table->date('enrollment_date');
            $table->text('special_requirements')->nullable();
            $table->unsignedBigInteger('enrolled_by');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['session_id']);
            $table->index(['trainee_id']);
            $table->index(['enrollment_status']);
            $table->index(['centre_id']);
            $table->unique(['session_id', 'trainee_id']);
        });

        // Session attendance for detailed tracking
        Schema::create('session_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->enum('attendance_status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->integer('participation_score')->nullable(); // 0-10 scale
            $table->text('session_progress_notes')->nullable();
            $table->text('behavioral_notes')->nullable();
            $table->boolean('goals_achieved')->default(false);
            $table->unsignedBigInteger('recorded_by');
            $table->string('centre_id');
            $table->timestamps();
            
            $table->index(['session_id']);
            $table->index(['trainee_id']);
            $table->index(['attendance_status']);
            $table->index(['centre_id']);
            $table->unique(['session_id', 'trainee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_attendance');
        Schema::dropIfExists('session_enrollments');
        Schema::dropIfExists('activity_enrollments');
        Schema::dropIfExists('activity_sessions');
    }
};