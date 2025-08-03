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
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('iep_id')->nullable(); // Link to specific IEP if applicable
            $table->string('report_title', 200);
            $table->enum('report_type', ['Weekly', 'Monthly', 'Quarterly', 'Annual', 'Custom'])->default('Monthly');
            $table->enum('report_period', ['Current', 'Previous', 'YearToDate', 'Custom'])->default('Current');
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->json('activity_progress')->nullable(); // Progress summary by activity
            $table->json('learning_outcomes_progress')->nullable(); // Progress summary by learning outcomes
            $table->json('iep_goals_progress')->nullable(); // IEP goals progress if applicable
            $table->json('attendance_summary')->nullable(); // Attendance statistics
            $table->json('competency_achievements')->nullable(); // Recently achieved competencies
            $table->json('recommendations')->nullable(); // Staff recommendations
            $table->text('overall_summary')->nullable(); // Overall progress narrative
            $table->text('strengths_observed')->nullable(); // Observed strengths
            $table->text('areas_for_improvement')->nullable(); // Areas needing focus
            $table->text('next_period_goals')->nullable(); // Goals for next period
            $table->enum('status', ['Draft', 'In Review', 'Approved', 'Shared'])->default('Draft');
            $table->boolean('parent_accessible')->default(false); // Can parents/guardians access this report?
            $table->timestamp('shared_with_parents_at')->nullable();
            $table->unsignedBigInteger('generated_by');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('iep_id')->references('id')->on('trainee_education_plans')->onDelete('set null');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['trainee_id', 'report_type', 'status']);
            $table->index(['period_start_date', 'period_end_date']);
            $table->index(['status', 'parent_accessible']);
            $table->index('report_type');
            $table->index('iep_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
    }
};
