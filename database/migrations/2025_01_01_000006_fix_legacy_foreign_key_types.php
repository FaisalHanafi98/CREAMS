<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sort after legacy table creation and before 000007 adds foreign keys.
     * Already aligned installations are unchanged; old migrations stay intact.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            throw new RuntimeException('Legacy reference alignment requires the supported MySQL database.');
        }

        $references = [
            'staff_attendances' => ['user_id' => 'staffs', 'approved_by' => 'staffs', 'marked_by_user_id' => 'staffs'],
            'attendance_alerts' => ['user_id' => 'staffs', 'trainee_id' => 'trainees', 'resolved_by' => 'staffs'],
            'assets' => ['assigned_to_user' => 'staffs'],
            'asset_maintenance' => ['asset_id' => 'assets'],
            'asset_maintenance_history' => ['asset_id' => 'assets', 'maintenance_id' => 'asset_maintenance'],
            'asset_movements' => ['asset_id' => 'assets', 'from_location_id' => 'asset_locations', 'to_location_id' => 'asset_locations', 'moved_by_user_id' => 'staffs'],
            'messages' => ['sender_id' => 'staffs'],
            'message_recipients' => ['recipient_id' => 'staffs'],
            'letter_templates' => ['created_by' => 'staffs'],
        ];

        // Validate every proposed alteration before issuing non-transactional DDL.
        $changes = [];
        $normalise = static fn (string $type): string => strtolower(preg_replace('/\(\d+\)/', '', $type));
        foreach ($references as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            $metadata = array_column(Schema::getColumns($table), null, 'name');
            foreach ($columns as $column => $parent) {
                if (! isset($metadata[$column]) || ! Schema::hasTable($parent)) {
                    continue;
                }
                $source = $metadata[$column];
                $targetType = $normalise(Schema::getColumnType($parent, 'id', true));
                $sourceType = $normalise($source['type']);
                if ($sourceType === $targetType) {
                    continue;
                }
                if ($targetType !== 'bigint unsigned' || ! preg_match('/^(?:tinyint|smallint|mediumint|int|bigint)(?: unsigned)?$/', $sourceType)) {
                    throw new RuntimeException("Review the unexpected reference types for {$table}.{$column} before migrating.");
                }
                if ($source['default'] !== null || DB::table($table)->where($column, '<', 0)->exists()) {
                    throw new RuntimeException("Review defaults or negative values in {$table}.{$column} before widening.");
                }
                foreach (Schema::getForeignKeys($table) as $key) {
                    if (in_array($column, $key['columns'], true)) {
                        throw new RuntimeException("Review the existing foreign key on {$table}.{$column} before changing its type.");
                    }
                }
                $changes[$table][$column] = $source;
            }
        }

        foreach ($changes as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $name => $metadata) {
                    $table->unsignedBigInteger($name)
                        ->nullable($metadata['nullable'])
                        ->comment($metadata['comment'] ?? '')
                        ->change();
                }
            });
        }
    }

    public function down(): void
    {
        // Narrowing can discard valid IDs and invalidate installed foreign keys.
        throw new RuntimeException('Reference type repair cannot be automatically narrowed. Restore a verified database backup or apply a separately reviewed forward migration.');
    }
};
