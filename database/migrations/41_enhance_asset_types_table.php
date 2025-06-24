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
        Schema::table('asset_types', function (Blueprint $table) {
            // Add enhanced fields for asset type management
            $table->text('description')->nullable()->after('category');
            $table->decimal('default_value', 10, 2)->nullable()->after('value');
            $table->integer('expected_lifespan')->nullable()->comment('Expected lifespan in days')->after('default_value');
            $table->boolean('maintenance_schedule')->default(false)->after('expected_lifespan');
            $table->integer('maintenance_interval')->nullable()->comment('Maintenance interval in days')->after('maintenance_schedule');
            
            // Depreciation settings
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'units_of_production'])->nullable()->after('maintenance_interval');
            $table->decimal('depreciation_rate', 5, 4)->nullable()->comment('Annual depreciation rate (0.0000-1.0000)')->after('depreciation_method');
            
            // Enhanced metadata
            $table->json('specifications')->nullable()->comment('Technical specifications as JSON')->after('image_path');
            $table->json('required_certifications')->nullable()->comment('Required certifications as JSON')->after('specifications');
            $table->integer('warranty_period')->nullable()->comment('Default warranty period in days')->after('required_certifications');
            $table->boolean('is_active')->default(true)->after('warranty_period');
            
            // Timestamps and soft deletes
            $table->softDeletes()->after('updated_at');
            
            // Indexes for performance
            $table->index(['category', 'is_active']);
            $table->index(['maintenance_schedule']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active']);
            $table->dropIndex(['maintenance_schedule']);
            $table->dropIndex(['is_active']);
            
            $table->dropSoftDeletes();
            $table->dropColumn([
                'description', 'default_value', 'expected_lifespan', 
                'maintenance_schedule', 'maintenance_interval',
                'depreciation_method', 'depreciation_rate',
                'specifications', 'required_certifications', 
                'warranty_period', 'is_active'
            ]);
        });
    }
};