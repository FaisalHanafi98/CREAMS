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
        Schema::create('activity_template_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('template_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('customizations')->nullable(); // Any template overrides
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->integer('sessions_generated')->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('activity_schedule_templates')->onDelete('cascade');
            
            // Indexes
            $table->index(['activity_id', 'status']);
            $table->index(['template_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_template_applications');
    }
};
