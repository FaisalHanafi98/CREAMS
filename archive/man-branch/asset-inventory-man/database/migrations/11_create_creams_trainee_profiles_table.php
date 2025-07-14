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
        Schema::create('trainee_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->string('guardian_name');
            $table->string('guardian_relationship');
            $table->string('guardian_phone');
            $table->string('guardian_email');
            $table->text('medical_history')->nullable();
            $table->timestamps();

            $table->foreign('trainee_id')
                ->references('id')
                ->on('trainees')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('trainee_profiles');
    }
};
