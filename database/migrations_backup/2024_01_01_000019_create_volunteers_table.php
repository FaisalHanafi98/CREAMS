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
        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('volunteer_name');
            $table->string('volunteer_email')->unique();
            $table->string('volunteer_phone');
            $table->text('volunteer_address');
            $table->date('volunteer_birth_date');
            $table->enum('volunteer_gender', ['Male', 'Female', 'Other']);
            $table->text('volunteer_skills')->nullable();
            $table->text('volunteer_experience')->nullable();
            $table->string('volunteer_availability');
            $table->enum('volunteer_status', ['active', 'inactive', 'pending'])->default('pending');
            $table->date('volunteer_start_date')->nullable();
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->timestamps();
            
            $table->index(['volunteer_status']);
            $table->index(['volunteer_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteers');
    }
};