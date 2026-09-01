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
            $table->string('admission_session')->nullable()->after('student_level_id');
            // Add index for efficient course cohort-based queries
            $table->index(['course_id', 'student_level_id', 'admission_session'], 'course_cohort_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_details', function (Blueprint $table) {
            $table->dropIndex('course_cohort_index');
            $table->dropColumn('admission_session');
        });
    }
};