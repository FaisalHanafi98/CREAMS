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
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            
            // Core movement information
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('from_location_id')->nullable();
            $table->unsignedBigInteger('to_location_id')->nullable();
            $table->unsignedBigInteger('moved_by_id');
            
            // Movement details
            $table->string('movement_reason');
            $table->timestamp('movement_date');
            $table->enum('movement_type', [
                'transfer', 'assignment', 'return', 'maintenance', 
                'loan', 'disposal', 'audit', 'emergency'
            ])->default('transfer');
            
            // Approval workflow
            $table->unsignedBigInteger('approved_by_id')->nullable();
            $table->timestamp('approval_date')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'pending', 'in_transit', 'completed', 'cancelled', 'failed'
            ])->default('completed');
            
            // Return tracking (for loans)
            $table->timestamp('estimated_return_date')->nullable();
            $table->timestamp('actual_return_date')->nullable();
            
            // Condition tracking
            $table->string('condition_before', 100)->nullable();
            $table->string('condition_after', 100)->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['asset_id', 'movement_date']);
            $table->index(['from_location_id']);
            $table->index(['to_location_id']);
            $table->index(['moved_by_id']);
            $table->index(['movement_type', 'status']);
            $table->index(['movement_date']);
            $table->index(['estimated_return_date']);
            
            // Foreign key constraints
            $table->foreign('asset_id')->references('id')->on('assets_enhanced')->onDelete('cascade');
            $table->foreign('from_location_id')->references('id')->on('asset_locations')->onDelete('set null');
            $table->foreign('to_location_id')->references('id')->on('asset_locations')->onDelete('set null');
            $table->foreign('moved_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};