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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_token')->unique();
            $table->string('device_type')->nullable(); // 'desktop', 'mobile', 'tablet'
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); // 'Windows', 'iOS', 'Android'
            $table->string('ip_address', 45)->nullable();
            $table->string('location')->nullable(); // City, Country
            $table->timestamp('last_activity');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_current']);
            $table->index(['session_token']);
            $table->index(['last_activity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};