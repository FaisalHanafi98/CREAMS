<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // For Admins table
        if (Schema::hasTable('admins')) {
            $this->addColumnsIfNotExist('admins', ['phone', 'address', 'bio', 'avatar']);
        }
        
        // For Supervisors table
        if (Schema::hasTable('supervisors')) {
            $this->addColumnsIfNotExist('supervisors', ['phone', 'address', 'bio', 'avatar']);
        }
        
        // For Teachers table
        if (Schema::hasTable('teachers')) {
            $this->addColumnsIfNotExist('teachers', ['phone', 'address', 'bio', 'avatar']);
        }
        
        // For AJKs table
        if (Schema::hasTable('ajks')) {
            $this->addColumnsIfNotExist('ajks', ['phone', 'address', 'bio', 'avatar']);
        }
    }

    private function addColumnsIfNotExist($tableName, $columns)
    {
        foreach ($columns as $column) {
            // Check if column exists before adding
            if (!Schema::hasColumn($tableName, $column)) {
                Schema::table($tableName, function (Blueprint $table) use ($column) {
                    if ($column === 'bio' || $column === 'address') {
                        $table->text($column)->nullable();
                    } else {
                        $table->string($column)->nullable();
                    }
                });
            }
        }
    }

    public function down()
    {
        // No need to remove columns that already existed
        $tables = ['admins', 'supervisors', 'teachers', 'ajks'];
        
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                // Only drop bio and avatar as they were likely added in this migration
                if (Schema::hasColumn($tableName, 'bio')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->dropColumn('bio');
                    });
                }
                
                if (Schema::hasColumn($tableName, 'avatar')) {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->dropColumn('avatar');
                    });
                }
            }
        }
    }
};