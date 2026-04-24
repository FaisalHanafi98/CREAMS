<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restores the asset_movements table if it was dropped.
 *
 * The 2025_09_29_130000_optimize_asset_management_tables migration dropped
 * asset_movements, incorrectly classifying it as unused. The AssetMovement
 * model, AssetController, AssetManagementService, AssetService, and
 * AssetRepositoryService all depend on this table in production.
 *
 * Production DB: optimize migration is Pending, so asset_movements still
 * exists. This migration is a no-op there (hasTable guard).
 *
 * Test DB (cream_test): optimize migration ran, so asset_movements is gone.
 * This migration recreates it with the same schema as the original.
 *
 * Schema matches 2025_01_01_000005_create_creams_asset_management_tables.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asset_movements')) {
            return;
        }

        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->integer('asset_id');
            $table->integer('from_location_id')->nullable();
            $table->integer('to_location_id')->nullable();
            $table->integer('moved_by_user_id')->nullable();
            $table->datetime('movement_date');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['asset_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};
