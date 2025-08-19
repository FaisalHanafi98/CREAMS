<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Asset Management Module.
     * Creates: asset_types, asset_categories, asset_locations, assets, asset_maintenance, asset_maintenance_history, asset_movements tables
     */
    public function up(): void
    {
        // 1. ASSET_TYPES TABLE
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name');
            $table->text('type_description')->nullable();
            $table->string('type_color', 7)->default('#6c757d');
            $table->json('type_attributes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
        });

        // 2. ASSET_CATEGORIES TABLE
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->text('category_description')->nullable();
            $table->foreignId('parent_category_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('parent_category_id');
            $table->index('is_active');
        });

        // 3. ASSET_LOCATIONS TABLE
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
            
            // Indexes
            $table->index('centre_id');
            $table->index('is_active');
        });

        // 4. ASSETS TABLE
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 50)->unique();
            $table->string('asset_name');
            $table->text('asset_description')->nullable();
            $table->foreignId('asset_type_id');
            $table->foreignId('asset_category_id');
            $table->string('centre_id', 10);
            $table->foreignId('current_location_id')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('model')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'broken'])->default('good');
            $table->enum('status', ['available', 'in_use', 'maintenance', 'retired', 'lost'])->default('available');
            $table->foreignId('assigned_to_user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('asset_type_id');
            $table->index('asset_category_id');
            $table->index('centre_id');
            $table->index('current_location_id');
            $table->index('condition');
            $table->index('status');
            $table->index('assigned_to_user_id');
        });

        // 5. ASSET_MAINTENANCE TABLE
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id');
            $table->string('maintenance_type', 50);
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('description')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->string('performed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('asset_id');
            $table->index('maintenance_type');
            $table->index('scheduled_date');
            $table->index('status');
        });

        // 6. ASSET_MAINTENANCE_HISTORY TABLE
        Schema::create('asset_maintenance_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id');
            $table->foreignId('maintenance_id')->nullable();
            $table->date('maintenance_date');
            $table->string('maintenance_type', 50);
            $table->text('description');
            $table->decimal('cost', 8, 2)->nullable();
            $table->string('performed_by');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('asset_id');
            $table->index('maintenance_id');
            $table->index('maintenance_date');
            $table->index('maintenance_type');
        });

        // 7. ASSET_MOVEMENTS TABLE
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id');
            $table->foreignId('from_location_id')->nullable();
            $table->foreignId('to_location_id');
            $table->foreignId('moved_by_user_id');
            $table->datetime('movement_date');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('asset_id');
            $table->index('from_location_id');
            $table->index('to_location_id');
            $table->index('moved_by_user_id');
            $table->index('movement_date');
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
        Schema::dropIfExists('asset_categories');
        Schema::dropIfExists('asset_types');
    }
};