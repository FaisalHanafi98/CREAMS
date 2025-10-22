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
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->string('name_ms')->nullable(); // Malay name
            $table->string('type')->default('public'); // public, state, observance
            $table->string('state')->nullable(); // For state-specific holidays (e.g., Selangor, Johor)
            $table->text('description')->nullable();
            $table->year('year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('date');
            $table->index('year');
            $table->index('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_holidays');
    }
};
