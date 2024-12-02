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
        Schema::create('post_utme_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('jamb_no')->unique();
            $table->string('name');
            $table->string('course');
            $table->string('jamb_score')->nullable();
            $table->string('acad_session');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_utme_uploads');
    }
};
