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
        Schema::create('assets_enhanced', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Foreign keys
            $table->unsignedBigInteger('asset_type_id')->nullable();
            $table->unsignedBigInteger('centre_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            
            // Asset details
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable()->unique();
            
            // Financial information
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            
            // Status and lifecycle
            $table->enum('status', ['available', 'in-use', 'maintenance', 'retired', 'disposed'])
                  ->default('available');
            
            // Warranty and maintenance
            $table->date('warranty_date')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->integer('maintenance_interval')->nullable()->comment('Days between maintenance');
            
            // Digital assets
            $table->string('image_path')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('rfid_tag', 50)->nullable()->unique();
            $table->string('barcode', 50)->nullable()->unique();
            
            // Additional information
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['status', 'centre_id']);
            $table->index(['asset_type_id', 'centre_id']);
            $table->index(['location_id']);
            $table->index(['assigned_to_id']);
            $table->index(['purchase_date']);
            $table->index(['next_maintenance_date']);
            
            // Foreign key constraints will be added in a separate migration after all tables are created
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets_enhanced');
    }
};