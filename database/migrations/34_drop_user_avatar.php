<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('users', 'user_avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_avatar');
            });
        }
        
        // Make sure avatar column exists
        if (!Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable();
            });
        }
    }

    public function down()
    {
        // Add back the user_avatar column if needed
        if (!Schema::hasColumn('users', 'user_avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('user_avatar')->nullable();
            });
        }
    }
};