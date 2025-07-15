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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('activity_id')->unique();
            $table->string('activity_name');
            $table->text('activity_description');
            $table->string('activity_type');
            $table->date('activity_date');
            $table->time('activity_start_time');
            $table->time('activity_end_time');
            $table->string('activity_location');
            $table->integer('max_participants')->default(20);
            $table->integer('current_participants')->default(0);
            $table->text('activity_goals')->nullable();
            $table->text('activity_outcomes')->nullable();
            $table->string('activity_image')->nullable();
            $table->json('required_resources')->nullable();
            $table->enum('activity_status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->string('centre_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->timestamps();
            
            $table->index(['activity_id']);
            $table->index(['centre_id']);
            $table->index(['activity_status']);
            $table->index(['activity_date']);
            $table->index(['category_id']);
            $table->index(['instructor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};