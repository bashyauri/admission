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
        Schema::table('student_transactions', function (Blueprint $table) {
            // Add foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            // Add foreign key constraint for student_levels_id
            $table->foreign('student_levels_id')->references('id')->on('student_levels')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['student_levels_id']);
        });
    }
};
