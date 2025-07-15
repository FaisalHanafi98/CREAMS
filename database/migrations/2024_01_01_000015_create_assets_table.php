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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('asset_name');
            $table->text('asset_description')->nullable();
            $table->unsignedBigInteger('asset_type_id');
            $table->string('asset_model')->nullable();
            $table->string('asset_brand')->nullable();
            $table->string('asset_serial_number')->nullable();
            $table->decimal('asset_value', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('supplier')->nullable();
            $table->text('warranty_info')->nullable();
            $table->enum('asset_condition', ['excellent', 'good', 'fair', 'poor', 'damaged'])->default('good');
            $table->enum('asset_status', ['available', 'in_use', 'maintenance', 'damaged', 'disposed'])->default('available');
            $table->string('asset_location');
            $table->string('centre_id');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('maintenance_notes')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->string('asset_image')->nullable();
            $table->json('asset_attributes')->nullable();
            $table->timestamps();
            
            $table->index(['asset_id']);
            $table->index(['asset_type_id']);
            $table->index(['asset_status']);
            $table->index(['centre_id']);
            $table->index(['assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};