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
        Schema::create('asset_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                     // e.g., Smartboard
            $table->string('category');                // e.g., Electronics
            $table->string('location')->nullable();
            $table->decimal('value', 10, 2)->nullable();
            $table->string('vendor')->nullable();
            $table->string('image_path')->nullable();  // image URL or path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_types');
    }
};
