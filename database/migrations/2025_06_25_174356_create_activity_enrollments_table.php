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
        Schema::create('activity_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('trainee_id');
            $table->date('enrollment_date');
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->enum('status', ['enrolled', 'active', 'completed', 'dropped', 'on_hold'])->default('enrolled');
            $table->text('progress_notes')->nullable();
            $table->decimal('attendance_rate', 5, 2)->default(0.00); // Percentage
            $table->integer('sessions_attended')->default(0);
            $table->integer('total_sessions')->default(0);
            $table->text('goals')->nullable(); // Individual goals for this trainee
            $table->text('achievements')->nullable(); // Progress achievements
            $table->unsignedBigInteger('enrolled_by')->nullable(); // User who enrolled the trainee
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('enrolled_by')->references('id')->on('users')->onDelete('set null');
            
            // Prevent duplicate enrollments
            $table->unique(['activity_id', 'trainee_id']);
            
            // Indexes for performance
            $table->index(['trainee_id', 'status']);
            $table->index(['activity_id', 'status']);
            $table->index(['status', 'enrollment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_enrollments');
    }
};