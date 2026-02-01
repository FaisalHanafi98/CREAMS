<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Optimize asset management tables:
     * 1. Drop unused tables (asset_maintenance_history, asset_movements)
     * 2. Add denormalized category/location names to assets table for easier querying
     * 3. Fix foreign key constraints
     * 4. Improve indexing
     */
    public function up(): void
    {
        // 1. BACKUP DATA BEFORE DROPPING TABLES (even though they're empty)
        $this->backupEmptyTables();

        // 2. DROP UNUSED TABLES
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('asset_maintenance_history');

        // 3. ADD DENORMALIZED FIELDS TO ASSETS TABLE
        Schema::table('assets', function (Blueprint $table) {
            // Add denormalized category and location names for easier querying
            $table->string('category_name')->nullable()->after('category_id');
            $table->string('location_name')->nullable()->after('location_id');
            $table->string('location_building')->nullable()->after('location_name');
            $table->string('location_room')->nullable()->after('location_building');

            // Fix assigned_to_user field type and add foreign key
            $table->unsignedBigInteger('assigned_to_user')->nullable()->change();

            // Add proper foreign key constraints
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('restrict');
            $table->foreign('type_id')->references('id')->on('asset_parents')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('asset_locations')->onDelete('set null');
            $table->foreign('assigned_to_user')->references('id')->on('staffs')->onDelete('set null');

            // Add better indexes
            $table->index(['status', 'condition'], 'idx_assets_status_condition');
            $table->index(['purchase_date', 'warranty_expiry'], 'idx_assets_dates');
        });

        // 4. POPULATE DENORMALIZED FIELDS
        $this->populateDenormalizedFields();

        // 5. IMPROVE ASSET_MAINTENANCE TABLE
        Schema::table('asset_maintenance', function (Blueprint $table) {
            // Fix asset_id field type and add foreign key
            $table->unsignedBigInteger('asset_id')->change();
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');

            // Add performed_by as foreign key to users table
            $table->unsignedBigInteger('performed_by_user_id')->nullable()->after('performed_by');

            // Add better indexes
            $table->index(['status', 'priority'], 'idx_maintenance_status_priority');
            $table->index(['scheduled_date', 'completed_date'], 'idx_maintenance_dates');
        });

        // 6. ADD FOREIGN KEY CONSTRAINTS TO LOOKUP TABLES
        Schema::table('asset_parents', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('restrict');
        });

        Schema::table('asset_locations', function (Blueprint $table) {
            $table->foreign('centre_id')->references('centre_id')->on('centres')->onDelete('set null');
        });
    }

    /**
     * Populate denormalized fields in assets table
     */
    private function populateDenormalizedFields(): void
    {
        // Update category names
        DB::statement("
            UPDATE assets a
            JOIN asset_categories ac ON a.category_id = ac.id
            SET a.category_name = ac.category_name
        ");

        // Update location information
        DB::statement("
            UPDATE assets a
            JOIN asset_locations al ON a.location_id = al.id
            SET
                a.location_name = al.location_name,
                a.location_building = al.building,
                a.location_room = al.room
        ");
    }

    /**
     * Backup empty tables data (for safety)
     */
    private function backupEmptyTables(): void
    {
        // Create backup tables for the empty tables we're dropping
        DB::statement("CREATE TABLE IF NOT EXISTS asset_movements_backup AS SELECT * FROM asset_movements");
        DB::statement("CREATE TABLE IF NOT EXISTS asset_maintenance_history_backup AS SELECT * FROM asset_maintenance_history");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign keys and indexes from assets table
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['type_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['assigned_to_user']);

            $table->dropIndex('idx_assets_status_condition');
            $table->dropIndex('idx_assets_dates');

            // Remove denormalized fields
            $table->dropColumn(['category_name', 'location_name', 'location_building', 'location_room']);

            // Revert assigned_to_user field type
            $table->integer('assigned_to_user')->nullable()->change();
        });

        // Remove foreign keys from other tables
        Schema::table('asset_maintenance', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropIndex('idx_maintenance_status_priority');
            $table->dropIndex('idx_maintenance_dates');
            $table->dropColumn('performed_by_user_id');
            $table->integer('asset_id')->change();
        });

        Schema::table('asset_parents', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        Schema::table('asset_locations', function (Blueprint $table) {
            $table->dropForeign(['centre_id']);
        });

        // Recreate the dropped tables (restore from backup if needed)
        $this->recreateDroppedTables();
    }

    /**
     * Recreate dropped tables for rollback
     */
    private function recreateDroppedTables(): void
    {
        // Recreate asset_movements table
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

            $table->index(['asset_id', 'movement_date']);
            $table->index(['from_location_id', 'to_location_id']);
        });

        // Recreate asset_maintenance_history table
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

            $table->index(['asset_id', 'maintenance_date']);
            $table->index(['maintenance_id', 'maintenance_type']);
        });
    }
};
