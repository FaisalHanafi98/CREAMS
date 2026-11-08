<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OPTIMIZER = '2025_09_29_130000_optimize_asset_management_tables';

    private const REFERENCES = [
        ['assets', 'category_id', 'asset_categories', 'id', 'cascade'],
        ['assets', 'type_id', 'asset_parents', 'id', 'set null'],
        ['assets', 'location_id', 'asset_locations', 'id', 'set null'],
        ['assets', 'assigned_to_user', 'staffs', 'id', 'set null'],
        ['asset_maintenance', 'asset_id', 'assets', 'id', 'cascade'],
        ['asset_parents', 'category_id', 'asset_categories', 'id', 'cascade'],
        ['asset_locations', 'centre_id', 'centres', 'centre_id', 'cascade'],
    ];

    public function up(): void
    {
        // A new pending migration must not remove an upgraded site's constraints.
        if ($this->optimizerHasRun()) {
            return;
        }
        foreach (['assets', 'asset_maintenance', 'asset_movements', 'asset_maintenance_history'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                throw new RuntimeException('Legacy asset refresh requires a reviewed data-preserving upgrade plan for a populated installation.');
            }
        }

        $drop = [];
        foreach (self::REFERENCES as [$table, $column, $parent, $parentColumn]) {
            foreach (Schema::getForeignKeys($table) as $key) {
                if (! in_array($column, $key['columns'], true)) {
                    continue;
                }
                if ($key['columns'] !== [$column] || $key['foreign_table'] !== $parent || $key['foreign_columns'] !== [$parentColumn]) {
                    throw new RuntimeException("Review the unexpected asset reference on {$table}.{$column} before migrating.");
                }
                $drop[$table][] = $key['name'];
            }
        }
        foreach ($drop as $tableName => $names) {
            Schema::table($tableName, function (Blueprint $table) use ($names): void {
                foreach ($names as $name) {
                    $table->dropForeign($name);
                }
            });
        }
    }

    public function down(): void
    {
        if ($this->optimizerHasRun()) {
            return;
        }
        foreach (self::REFERENCES as [$tableName, $column, $parent, $parentColumn, $onDelete]) {
            $keys = Schema::getForeignKeys($tableName);
            if (array_filter($keys, fn (array $key) => in_array($column, $key['columns'], true))) {
                continue;
            }
            if (Schema::getColumnType($tableName, $column, true) !== Schema::getColumnType($parent, $parentColumn, true)) {
                throw new RuntimeException('Restore compatible asset reference types before restoring legacy constraints.');
            }
            Schema::table($tableName, function (Blueprint $table) use ($column, $parent, $parentColumn, $onDelete): void {
                $table->foreign($column)->references($parentColumn)->on($parent)->onDelete($onDelete);
            });
        }
    }

    private function optimizerHasRun(): bool
    {
        return DB::table('migrations')->where('migration', self::OPTIMIZER)->exists();
    }
};
