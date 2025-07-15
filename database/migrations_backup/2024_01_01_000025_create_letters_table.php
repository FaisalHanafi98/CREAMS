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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('type');
            $table->string('recipient_name');
            $table->text('content');
            $table->unsignedBigInteger('trainee_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->string('centre_id');
            $table->date('letter_date');
            $table->enum('status', ['draft', 'sent', 'archived'])->default('draft');
            $table->timestamps();
            
            $table->index(['reference_number']);
            $table->index(['type']);
            $table->index(['trainee_id']);
            $table->index(['centre_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};