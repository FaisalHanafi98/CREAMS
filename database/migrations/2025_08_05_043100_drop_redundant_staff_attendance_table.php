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
        // Drop the redundant staff_attendance table (singular)
        // We're keeping staff_attendances (plural) as the main table
        Schema::dropIfExists('staff_attendance');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the staff_attendance table if needed
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'excused'])->default('present');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('marked_by')->nullable();
            $table->string('centre_id');
            $table->boolean('is_self_marked')->default(false);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['user_id', 'attendance_date']);
            $table->index(['attendance_date', 'centre_id']);
            $table->index(['user_id', 'attendance_date']);
        });
    }
};
