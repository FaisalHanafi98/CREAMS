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
        Schema::create('session_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('trainee_id');
            $table->date('enrollment_date');
            $table->enum('enrollment_status', ['enrolled', 'attended', 'absent', 'excused'])->default('enrolled');
            $table->text('enrollment_notes')->nullable();
            $table->decimal('participation_score', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->unsignedBigInteger('enrolled_by');
            $table->timestamps();
            
            $table->index(['session_id']);
            $table->index(['trainee_id']);
            $table->index(['enrollment_status']);
            $table->index(['enrollment_date']);
            $table->unique(['session_id', 'trainee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_enrollments');
    }
};