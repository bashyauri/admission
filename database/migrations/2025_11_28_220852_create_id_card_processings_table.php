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
        Schema::create('id_card_processings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('academic_session'); // e.g. "2025/2026"
            $table->enum('status', ['unprocessed', 'processed'])->default('unprocessed');
            $table->enum('print_status', ['not_printed', 'printed'])->default('not_printed');
            $table->timestamps();

            $table->unique(['user_id', 'academic_session']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('id_card_processings');
    }
};
