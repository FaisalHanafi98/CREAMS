<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('trainee_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->string('activity_name');
            $table->string('activity_type');
            $table->text('activity_description')->nullable();
            $table->date('activity_date');
            $table->timestamps();
            
            $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
            $table->unique(['trainee_id', 'activity_name', 'activity_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trainee_activities');
    }
};
