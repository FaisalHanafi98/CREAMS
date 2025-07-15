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
            $table->enum('enrollment_status', ['enrolled', 'completed', 'dropped', 'pending'])->default('enrolled');
            $table->text('enrollment_notes')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->integer('attendance_count')->default(0);
            $table->date('completion_date')->nullable();
            $table->text('completion_notes')->nullable();
            $table->unsignedBigInteger('enrolled_by');
            $table->timestamps();
            
            $table->index(['activity_id']);
            $table->index(['trainee_id']);
            $table->index(['enrollment_status']);
            $table->index(['enrollment_date']);
            $table->unique(['activity_id', 'trainee_id']);
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