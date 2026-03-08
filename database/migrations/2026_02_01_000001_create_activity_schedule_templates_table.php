<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create activity_schedule_templates and activity_template_applications tables.
     * These back the fully-implemented template feature (model + controller + views)
     * that was missing its migration.
     */
    public function up()
    {
        Schema::create('activity_schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->text('description')->nullable();
            $table->integer('sessions_per_week');
            $table->integer('duration_weeks');
            $table->integer('session_length_minutes');
            $table->json('days_of_week');
            $table->json('time_slots');
            $table->enum('template_type', ['weekly', 'intensive', 'flexible', 'custom']);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('centre_id')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('staffs')->onDelete('set null');
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });

        Schema::create('activity_template_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('template_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('customizations')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->integer('sessions_generated')->default(0);
            $table->timestamps();

            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('activity_schedule_templates')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_template_applications');
        Schema::dropIfExists('activity_schedule_templates');
    }
};
