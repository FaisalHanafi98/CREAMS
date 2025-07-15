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
        Schema::create('centres', function (Blueprint $table) {
            $table->id();
            $table->string('centre_id')->unique();
            $table->string('centre_name');
            $table->text('centre_address');
            $table->string('centre_phone');
            $table->string('centre_email');
            $table->string('centre_capacity');
            $table->string('centre_manager');
            $table->string('centre_manager_contact');
            $table->enum('centre_status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->text('centre_description')->nullable();
            $table->json('centre_facilities')->nullable();
            $table->string('centre_image')->nullable();
            $table->decimal('centre_latitude', 10, 8)->nullable();
            $table->decimal('centre_longitude', 11, 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['centre_id']);
            $table->index(['centre_status']);
            $table->index(['is_active']);
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