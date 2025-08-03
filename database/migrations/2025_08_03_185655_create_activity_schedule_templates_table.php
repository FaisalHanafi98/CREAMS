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
        Schema::create('activity_schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name', 100)->index();
            $table->text('description')->nullable();
            $table->integer('sessions_per_week')->default(1);
            $table->integer('duration_weeks')->default(4);
            $table->integer('session_length_minutes')->default(60);
            $table->json('days_of_week')->nullable(); // ["Monday", "Wednesday", "Friday"]
            $table->json('time_slots')->nullable(); // [{"start": "09:00", "end": "10:00"}]
            $table->string('template_type')->default('custom'); // custom, weekly, intensive, flexible
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('centre_id', 50)->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['centre_id', 'is_active']);
            $table->index(['template_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_schedule_templates');
    }
};
