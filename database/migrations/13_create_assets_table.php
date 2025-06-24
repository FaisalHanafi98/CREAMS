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
        Schema::create('assets', function (Blueprint $table) {
            $table->string('asset_id')->unique();
            $table->string('asset_name');
            $table->string('asset_type');
            $table->string('asset_brand');
            $table->string('asset_avatar');
            $table->integer('asset_price');
            $table->integer('asset_quantity');       
            $table->timestamp('asset_last_updated')->nullable();
            $table->string('centre_name')->nullable();
            $table->foreign('centre_name')->references('centre_name')->on('centres')->onDelete('set null');
            $table->text('asset_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
