<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->enum('relationship', ['Parent', 'Guardian', 'Sibling', 'Other']);
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_access_portal')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            
            $table->foreign('trainee_id')->references('id')->on('trainees');
            $table->index(['email', 'trainee_id']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('guardians');
    }
};