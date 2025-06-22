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
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            
            // Core maintenance information
            $table->unsignedBigInteger('asset_id');
            $table->enum('maintenance_type', [
                'preventive', 'corrective', 'emergency', 'inspection',
                'calibration', 'upgrade', 'cleaning', 'safety_check'
            ]);
            
            // Scheduling
            $table->timestamp('scheduled_date');
            $table->timestamp('completed_date')->nullable();
            
            // Personnel
            $table->unsignedBigInteger('performed_by_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            
            // Status and priority
            $table->enum('status', [
                'scheduled', 'in_progress', 'on_hold', 'completed', 'cancelled', 'failed'
            ])->default('scheduled');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Work details
            $table->text('description')->nullable();
            
            // Cost tracking
            $table->decimal('cost', 10, 2)->nullable();
            $table->decimal('parts_cost', 10, 2)->nullable();
            $table->decimal('labor_cost', 10, 2)->nullable();
            
            // Downtime tracking
            $table->timestamp('downtime_start')->nullable();
            $table->timestamp('downtime_end')->nullable();
            
            // Next maintenance scheduling
            $table->timestamp('next_maintenance_date')->nullable();
            
            // Compliance and certification
            $table->boolean('certification_required')->default(false);
            $table->boolean('certification_obtained')->default(false);
            $table->boolean('warranty_work')->default(false);
            
            // Work order management
            $table->string('work_order_number', 50)->nullable();
            
            // Additional information
            $table->text('notes')->nullable();
            
            // Maintenance categorization
            $table->boolean('preventive_maintenance')->default(false);
            $table->boolean('compliance_check')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['asset_id', 'scheduled_date']);
            $table->index(['status', 'priority']);
            $table->index(['maintenance_type']);
            $table->index(['scheduled_date']);
            $table->index(['next_maintenance_date']);
            $table->index(['performed_by_id']);
            $table->index(['work_order_number']);
            
            // Foreign key constraints
            $table->foreign('asset_id')->references('id')->on('assets_enhanced')->onDelete('cascade');
            $table->foreign('performed_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
    }
};