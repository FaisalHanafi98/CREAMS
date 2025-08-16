<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Fix assets table schema to match Asset model
     */
    public function up(): void
    {
        // Add missing columns that the Asset model expects
        Schema::table('assets', function (Blueprint $table) {
            // Add columns the model expects but table doesn't have
            $table->string('asset_code')->nullable()->after('id');
            $table->string('name')->nullable()->after('asset_name');
            $table->text('description')->nullable()->after('asset_description');
            $table->unsignedBigInteger('category_id')->nullable()->after('asset_type_id');
            $table->string('brand')->nullable()->after('manufacturer');
            $table->integer('warranty_months')->default(12)->after('warranty_expiry');
            $table->date('assigned_date')->nullable()->after('assigned_to');
            $table->decimal('depreciation_rate', 5, 2)->default(20.00)->after('current_value');
            $table->json('specifications')->nullable()->after('depreciation_rate');
            $table->json('images')->nullable()->after('asset_image');
            $table->text('notes')->nullable()->after('maintenance_notes');
            $table->unsignedBigInteger('created_by')->nullable()->after('notes');
        });

        // Update new columns with data from existing columns where possible
        DB::statement('UPDATE assets SET 
            asset_code = asset_id,
            name = asset_name,
            description = asset_description,
            category_id = asset_type_id,
            brand = manufacturer,
            notes = maintenance_notes,
            images = JSON_ARRAY(asset_image) WHERE asset_image IS NOT NULL
        ');

        // Update asset_code to be unique and not null
        DB::statement('UPDATE assets SET asset_code = CONCAT("ASSET", LPAD(id, 6, "0")) WHERE asset_code IS NULL');
        
        Schema::table('assets', function (Blueprint $table) {
            $table->string('asset_code')->unique()->change();
        });

        // Add foreign key constraints for category_id
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('asset_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'asset_code', 'name', 'description', 'category_id', 'brand', 
                'warranty_months', 'assigned_date', 'depreciation_rate', 
                'specifications', 'images', 'notes', 'created_by'
            ]);
        });
    }
};