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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('marked_by');
            $table->timestamp('marked_at')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('restrict');
            
            // Indexes for performance
            $table->index(['trainee_id', 'date']);
            $table->index(['activity_id', 'date']);
            $table->index(['date', 'status']);
            
            // Unique constraint to prevent duplicate attendance records
            $table->unique(['trainee_id', 'activity_id', 'session_id', 'date'], 'unique_attendance_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
