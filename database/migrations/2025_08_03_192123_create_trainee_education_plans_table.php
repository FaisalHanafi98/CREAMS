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
        Schema::create('trainee_education_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->string('plan_name', 200);
            $table->text('plan_description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('review_date')->nullable(); // Next review date
            $table->json('overall_goals')->nullable(); // Array of overall goals
            $table->json('strengths')->nullable(); // Trainee strengths
            $table->json('challenges')->nullable(); // Areas needing support
            $table->json('support_services')->nullable(); // Required support services
            $table->enum('status', ['Active', 'Completed', 'Suspended', 'Under Review'])->default('Active');
            $table->enum('plan_type', ['Annual', 'Quarterly', 'Custom'])->default('Annual');
            $table->decimal('target_completion_percentage', 5, 2)->default(80.00);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['trainee_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('review_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainee_education_plans');
    }
};
