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
        Schema::create('iep_activity_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iep_id');
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('learning_outcome_id')->nullable(); // Link to specific learning outcome
            $table->string('goal_title', 200);
            $table->text('goal_description');
            $table->date('target_start_date');
            $table->date('target_completion_date');
            $table->enum('progress_tracking_method', ['Attendance', 'Competency', 'Assessment', 'Mixed'])->default('Mixed');
            $table->decimal('target_percentage', 5, 2)->default(80.00);
            $table->enum('priority_level', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->enum('goal_status', ['Not Started', 'In Progress', 'Achieved', 'Modified', 'Discontinued'])->default('Not Started');
            $table->json('success_criteria')->nullable(); // Array of success criteria
            $table->json('accommodation_strategies')->nullable(); // Special accommodations needed
            $table->text('notes')->nullable();
            $table->decimal('current_progress_percentage', 5, 2)->default(0.00);
            $table->timestamp('last_progress_update')->nullable();
            $table->unsignedBigInteger('assigned_staff_id')->nullable(); // Primary responsible staff
            $table->timestamps();

            // Foreign keys
            $table->foreign('iep_id')->references('id')->on('trainee_education_plans')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('learning_outcome_id')->references('id')->on('learning_outcomes')->onDelete('set null');
            $table->foreign('assigned_staff_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['iep_id', 'goal_status']);
            $table->index(['activity_id', 'target_completion_date']);
            $table->index(['target_completion_date', 'priority_level']);
            $table->index('goal_status');
            $table->index('assigned_staff_id');
            
            // Unique constraint to prevent duplicate goals for same IEP-Activity-Outcome combination
            $table->unique(['iep_id', 'activity_id', 'learning_outcome_id'], 'iep_activity_outcome_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iep_activity_goals');
    }
};
