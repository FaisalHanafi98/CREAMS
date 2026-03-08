<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainee_education_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->string('plan_name');
            $table->text('plan_description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('review_date')->nullable();
            $table->json('overall_goals')->nullable();
            $table->json('strengths')->nullable();
            $table->json('challenges')->nullable();
            $table->json('support_services')->nullable();
            $table->string('status', 20)->default('Active');
            $table->string('plan_type', 20)->default('Annual');
            $table->decimal('target_completion_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('staffs')->onDelete('set null');
            $table->foreign('last_updated_by')->references('id')->on('staffs')->onDelete('set null');
        });

        Schema::create('iep_activity_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iep_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('learning_outcome_id')->nullable();
            $table->string('goal_title');
            $table->text('goal_description')->nullable();
            $table->date('target_start_date')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->string('progress_tracking_method', 30)->nullable();
            $table->decimal('target_percentage', 5, 2)->default(0);
            $table->string('priority_level', 20)->default('Medium');
            $table->string('goal_status', 30)->default('Not Started');
            $table->json('success_criteria')->nullable();
            $table->json('accommodation_strategies')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('current_progress_percentage', 5, 2)->default(0);
            $table->timestamp('last_progress_update')->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->timestamps();

            $table->foreign('iep_id')->references('id')->on('trainee_education_plans')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('set null');
            $table->foreign('assigned_user_id')->references('id')->on('staffs')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iep_activity_goals');
        Schema::dropIfExists('trainee_education_plans');
    }
};
