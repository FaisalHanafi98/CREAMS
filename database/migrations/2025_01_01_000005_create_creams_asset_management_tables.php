<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreamsAssetManagementTables extends Migration
{
    /**
     * CREAMS Asset Management Migration
     * Tables: asset_categories, asset_parents, asset_locations, assets, asset_maintenance, asset_maintenance_history, asset_movements
     * Dependencies: Foundation Management
     */
    public function up(): void
    {
        // Skip if tables already exist (preserves current logic)
        if (Schema::hasTable('asset_categories')) {
            return;
        }

        // 1. ASSET CATEGORIES - Equipment classification (preserves current structure)
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->text('category_description')->nullable();
            // Removed parent_category_id - flat category structure preferred
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        // 2. ASSET TYPES - Equipment types (preserves current structure)
        Schema::create('asset_parents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('type_description')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('image_path')->nullable();
            $table->boolean('requires_maintenance')->default(false);
            $table->integer('default_maintenance_interval_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
        });

        // 3. ASSET LOCATIONS - Storage locations (preserves current structure)
        Schema::create('asset_locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_name');
            $table->text('location_description')->nullable();
            $table->string('centre_id', 10)->nullable();
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('room')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['centre_id', 'is_active']);
        });

        // 4. ASSETS - Equipment inventory (preserves current structure)
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('centre_id', 10);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('asset_tag')->unique();
            $table->string('asset_name');
            $table->text('asset_description')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'damaged', 'disposed'])->default('good');
            $table->enum('status', ['available', 'in_use', 'maintenance', 'retired', 'missing'])->default('available');
            $table->integer('assigned_to_user')->nullable();
            $table->text('notes')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'centre_id', 'status']);
            $table->index(['location_id', 'condition']);
        });

        // 5. ASSET MAINTENANCE - Maintenance scheduling (preserves current structure)
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->integer('asset_id');
            $table->string('maintenance_type', 50);
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->text('description')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->string('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['asset_id', 'scheduled_date', 'status']);
            $table->index(['maintenance_type', 'priority']);
        });

        // 6. ASSET MAINTENANCE HISTORY - Historical records (preserves current structure)
        Schema::create('asset_maintenance_history', function (Blueprint $table) {
            $table->id();
            $table->integer('asset_id');
            $table->integer('maintenance_id')->nullable();
            $table->date('maintenance_date');
            $table->string('maintenance_type', 50);
            $table->text('description');
            $table->decimal('cost', 8, 2)->nullable();
            $table->string('performed_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['asset_id', 'maintenance_date']);
            $table->index(['maintenance_id', 'maintenance_type']);
        });

        // 7. ASSET MOVEMENTS - Movement tracking (preserves current structure)
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->integer('asset_id');
            $table->integer('from_location_id')->nullable();
            $table->integer('to_location_id');
            $table->integer('moved_by_user_id');
            $table->datetime('movement_date');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['asset_id', 'movement_date']);
            $table->index(['from_location_id', 'to_location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('asset_maintenance_history');
        Schema::dropIfExists('asset_maintenance');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_locations');
        Schema::dropIfExists('asset_parents');
        Schema::dropIfExists('asset_categories');
    }
}
