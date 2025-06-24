<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists first
        if (Schema::hasTable('personal_access_tokens')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                // Check if column doesn't exist first
                if (!Schema::hasColumn('personal_access_tokens', 'new_column')) {
                    $table->string('new_column')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('personal_access_tokens') && 
            Schema::hasColumn('personal_access_tokens', 'new_column')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->dropColumn('new_column');
            });
        }
    }
};