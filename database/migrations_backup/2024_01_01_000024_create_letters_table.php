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
            $table->string('letter_reference')->unique();
            $table->string('letter_subject');
            $table->text('letter_content');
            $table->string('letter_type');
            $table->unsignedBigInteger('recipient_id');
            $table->string('recipient_type');
            $table->unsignedBigInteger('template_id')->nullable();
            $table->enum('letter_status', ['draft', 'sent', 'delivered', 'archived'])->default('draft');
            $table->date('letter_date');
            $table->date('sent_date')->nullable();
            $table->string('letter_file_path')->nullable();
            $table->json('letter_data')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['letter_reference']);
            $table->index(['letter_type']);
            $table->index(['recipient_id']);
            $table->index(['letter_status']);
            $table->index(['letter_date']);
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