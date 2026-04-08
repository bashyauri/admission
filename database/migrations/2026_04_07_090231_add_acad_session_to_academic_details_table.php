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
        Schema::table('academic_details', function (Blueprint $table) {
            $table->string('acad_session')->nullable()->after('student_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_details', function (Blueprint $table) {
            $table->dropColumn('acad_session');
        });
    }
};
