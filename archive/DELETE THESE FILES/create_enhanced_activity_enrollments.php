<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnhancedActivityEnrollments extends Migration
{
    public function up()
    {
        Schema::create('activity_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_session_id')->constrained('activity_sessions')->onDelete('cascade');
            $table->foreignId('trainee_id')->constrained('trainees')->onDelete('cascade');
            $table->foreignId('enrolled_by')->constrained('users')->onDelete('restrict');
            $table->enum('enrollment_status', ['active', 'inactive', 'completed', 'withdrawn', 'pending']);
            $table->date('enrollment_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('enrollment_notes')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            $table->json('individual_goals')->nullable(); // Specific goals for this trainee
            $table->timestamps();
            
            // Prevent duplicate enrollments
            $table->unique(['activity_session_id', 'trainee_id'], 'unique_enrollment');
            
            // Indexes for performance
            $table->index(['trainee_id', 'enrollment_status']);
            $table->index(['activity_session_id', 'enrollment_status']);
            $table->index('enrollment_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_enrollments');
    }
}