<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Centre Management Module.
     * Creates: centres table
     */
    public function up(): void
    {
        // CENTRES TABLE
        Schema::create('centres', function (Blueprint $table) {
            $table->string('centre_id', 10)->primary();
            $table->string('centre_name');
            $table->text('centre_address')->nullable();
            $table->string('centre_phone', 20)->nullable();
            $table->string('centre_email')->nullable();
            $table->string('centre_capacity', 10)->nullable();
            $table->string('centre_manager')->nullable();
            $table->string('centre_manager_contact', 20)->nullable();
            $table->string('centre_status', 50)->default('active');
            $table->text('centre_description')->nullable();
            $table->json('centre_facilities')->nullable();
            $table->time('opening_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('centre_status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centres');
    }
};