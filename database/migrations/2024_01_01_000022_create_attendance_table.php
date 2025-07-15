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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('attendance_status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('attendance_notes')->nullable();
            $table->decimal('participation_score', 5, 2)->nullable();
            $table->unsignedBigInteger('recorded_by');
            $table->timestamps();
            
            $table->index(['trainee_id']);
            $table->index(['activity_id']);
            $table->index(['session_id']);
            $table->index(['class_id']);
            $table->index(['attendance_date']);
            $table->index(['attendance_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};