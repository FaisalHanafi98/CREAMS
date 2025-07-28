<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            
            // Who the attendance is for
            $table->unsignedBigInteger('user_id');
            
            // Who marked the attendance (can be same as user_id for self-marking)
            $table->unsignedBigInteger('marked_by_user_id');
            $table->string('marked_by_email');
            
            // Date and time as separate columns as requested
            $table->date('attendance_date');
            $table->time('attendance_time');
            
            // Centre isolation
            $table->string('centre_id', 50);
            
            // Attendance status
            $table->enum('status', ['present', 'absent', 'late', 'sick_leave', 'emergency_leave', 'authorized_leave'])->default('present');
            
            // Additional fields
            $table->text('remarks')->nullable();
            $table->enum('attendance_type', ['check_in', 'check_out'])->default('check_in');
            
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('marked_by_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('restrict');
            
            // Indexes for performance
            $table->index(['user_id', 'attendance_date']);
            $table->index(['centre_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
            $table->index(['marked_by_user_id']);
            
            // Unique constraint to prevent duplicate daily attendance records
            $table->unique(['user_id', 'attendance_date', 'attendance_type'], 'unique_daily_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendances');
    }
};
