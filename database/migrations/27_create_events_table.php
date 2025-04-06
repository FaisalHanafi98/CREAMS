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
        Schema::create('events', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->date('date');
        $table->string('start_time');
        $table->string('end_time');
        $table->string('location');
        $table->text('description');
        $table->string('organizer');
        $table->string('contact_person');
        $table->string('contact_email');
        $table->string('contact_phone');
        $table->integer('max_participants');
        $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
        $table->string('centre_id');  // Change to string type
        $table->json('schedule')->nullable();
        $table->timestamps();
        
        // Add the foreign key constraint to reference centre_id
        $table->foreign('centre_id')->references('centre_id')->on('centres');
    });

        // Pivot table for event participants
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        // Pivot table for event volunteers
        Schema::create('event_volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_volunteers');
        Schema::dropIfExists('event_participants');
        Schema::dropIfExists('events');
    }
};