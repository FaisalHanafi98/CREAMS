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
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('asset_name');
            $table->string('asset_type');
            $table->string('asset_brand')->nullable();
            $table->string('centre_name');
            $table->decimal('asset_price', 10, 2)->nullable();
            $table->integer('asset_quantity')->default(1);
            $table->text('asset_note')->nullable();
            $table->string('asset_avatar')->nullable();
            $table->string('asset_condition')->nullable();
            $table->text('asset_description')->nullable();
            $table->string('asset_location')->nullable();
            $table->timestamp('asset_last_updated')->nullable();
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->timestamps();
            
            $table->index(['asset_id']);
            $table->index(['centre_name']);
            $table->index(['asset_type']);
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