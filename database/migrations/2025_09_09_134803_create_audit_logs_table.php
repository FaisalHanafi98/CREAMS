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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_role', 50)->nullable();
            $table->string('action', 50); // create, update, delete, login, logout, etc.
            $table->string('table', 100)->nullable(); // affected table name
            $table->string('record_id', 50)->nullable(); // affected record ID
            $table->json('old_values')->nullable(); // previous values
            $table->json('new_values')->nullable(); // new values
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('centre_id', 10)->nullable(); // for multi-tenant filtering
            $table->text('description')->nullable(); // human-readable description
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['table', 'record_id']);
            $table->index('centre_id');
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('staffs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
