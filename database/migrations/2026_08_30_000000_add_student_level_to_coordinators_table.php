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
        Schema::table('coordinators', function (Blueprint $table) {
            if (!Schema::hasColumn('coordinators', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('coordinators', 'student_level_id')) {
                $table->foreignId('student_level_id')->nullable()->after('course_id')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('coordinators', 'academic_session')) {
                $table->string('academic_session')->nullable()->after('student_level_id');
            }
            
            $table->unique(['course_id', 'student_level_id', 'academic_session'], 'course_cohort_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinators', function (Blueprint $table) {
            $table->dropUnique('course_cohort_unique');
            $table->dropForeign(['student_level_id']);
            $table->dropForeign(['course_id']);
            $table->dropColumn(['student_level_id', 'academic_session', 'course_id']);
        });
    }
};