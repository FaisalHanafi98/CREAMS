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
        Schema::create('trainee_competency_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('learning_outcome_id');
            $table->enum('current_level', ['Not Started', 'In Progress', 'Achieved', 'Mastered'])->default('Not Started');
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->timestamp('last_assessed_at')->nullable();
            $table->unsignedBigInteger('assessed_by')->nullable();
            $table->text('notes')->nullable();
            $table->json('assessment_data')->nullable(); // Store assessment details
            $table->timestamps();

            // Foreign keys
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('learning_outcome_id')->references('id')->on('learning_outcomes')->onDelete('cascade');
            $table->foreign('assessed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['trainee_id', 'learning_outcome_id'], 'tcp_trainee_outcome_idx');
            $table->index(['current_level', 'progress_percentage'], 'tcp_level_progress_idx');
            $table->index('last_assessed_at', 'tcp_assessed_at_idx');
            
            // Unique constraint to prevent duplicate progress records
            $table->unique(['trainee_id', 'learning_outcome_id'], 'tcp_trainee_outcome_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainee_competency_progress');
    }
};
