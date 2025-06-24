<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            
            // Enrollment details
            $table->datetime('enrolled_at');
            $table->unsignedBigInteger('enrolled_by');
            $table->enum('enrollment_status', [
                'enrolled',
                'waitlisted',
                'cancelled',
                'completed'
            ])->default('enrolled');
            
            // Attendance
            $table->enum('attendance_status', [
                'pending',
                'present',
                'absent',
                'late',
                'excused'
            ])->default('pending');
            $table->datetime('checked_in_at')->nullable();
            
            // Performance
            $table->integer('participation_score')->nullable();
            $table->text('progress_notes')->nullable();
            $table->json('skills_demonstrated')->nullable();
            
            // Special requirements
            $table->text('special_requirements')->nullable();
            $table->boolean('requires_assistance')->default(false);
            
            $table->timestamps();
            
            // Composite unique key
            $table->unique(['session_id', 'trainee_id']);
            
            // Indexes
            $table->index(['trainee_id', 'enrollment_status']);
            $table->index('attendance_status');
            
            // Foreign keys
            $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('enrolled_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_enrollments');
    }
};