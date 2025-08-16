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
            $table->string('event_name');
            $table->text('event_description');
            $table->string('event_type');
            $table->date('event_date');
            $table->time('event_start_time');
            $table->time('event_end_time');
            $table->string('event_location');
            $table->integer('max_participants')->nullable();
            $table->integer('current_participants')->default(0);
            $table->decimal('event_fee', 10, 2)->default(0);
            $table->string('event_image')->nullable();
            $table->text('event_requirements')->nullable();
            $table->enum('event_status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
            $table->string('centre_id');
            $table->unsignedBigInteger('organizer_id');
            $table->timestamps();
            
            $table->index(['event_status']);
            $table->index(['event_date']);
            $table->index(['centre_id']);
            $table->index(['organizer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};