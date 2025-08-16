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
        Schema::create('activity_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->unsignedBigInteger('activity_id');
            $table->string('session_name');
            $table->text('session_description')->nullable();
            $table->date('session_date');
            $table->time('session_start_time');
            $table->time('session_end_time');
            $table->string('session_location');
            $table->integer('max_participants')->default(20);
            $table->integer('current_participants')->default(0);
            $table->text('session_objectives')->nullable();
            $table->text('session_notes')->nullable();
            $table->json('session_materials')->nullable();
            $table->enum('session_status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->unsignedBigInteger('instructor_id');
            $table->timestamps();
            
            $table->index(['session_id']);
            $table->index(['activity_id']);
            $table->index(['session_status']);
            $table->index(['session_date']);
            $table->index(['instructor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_sessions');
    }
};