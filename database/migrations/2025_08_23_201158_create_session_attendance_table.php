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
        Schema::create('session_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('marked_by_staff_id');
            $table->enum('attendance_status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->time('check_in_time')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('participation_score', 3, 1)->nullable(); // 0.0 to 10.0 score
            $table->timestamps();
            
            $table->index(['session_id', 'trainee_id']);
            $table->index('trainee_id');
            $table->index('marked_by_staff_id');
            $table->index('attendance_status');
            $table->unique(['session_id', 'trainee_id']); // Prevent duplicate attendance for same session
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_attendance');
    }
};
