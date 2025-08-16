<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Centre Management Module
     * Module: Centre Management (Multi-tenancy Foundation)
     * Priority: 002 - Critical (Multi-tenant Architecture Base)
     */
    public function up(): void
    {
        // Main centres table - Foundation for multi-tenancy
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
            $table->time('opening_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['centre_id']);
            $table->index(['centre_status']);
            $table->index(['is_active']);
        });

        // Asset types for centre equipment
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name');
            $table->text('type_description')->nullable();
            $table->string('type_category');
            $table->boolean('requires_maintenance')->default(false);
            $table->integer('default_maintenance_interval_days')->nullable();
            $table->timestamps();
            
            $table->index(['type_category']);
        });

        // Assets table for centre inventory
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('asset_name');
            $table->text('asset_description')->nullable();
            $table->unsignedBigInteger('asset_type_id');
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->string('location');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])->default('good');
            $table->enum('status', ['active', 'inactive', 'maintenance', 'disposed'])->default('active');
            $table->string('centre_id');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('maintenance_notes')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->string('warranty_expiry')->nullable();
            $table->string('asset_image')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('rfid_tag')->nullable();
            $table->string('priority')->nullable();
            $table->timestamps();
            
            $table->index(['asset_id']);
            $table->index(['centre_id']);
            $table->index(['status']);
            $table->index(['condition']);
            $table->index(['asset_type_id']);
            $table->index(['assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_types');
        Schema::dropIfExists('centres');
    }
};