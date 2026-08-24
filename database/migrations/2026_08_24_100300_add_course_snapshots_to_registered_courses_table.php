<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registered_courses', function (Blueprint $table) {
            $table->string('course_code_snapshot')->nullable()->after('student_level_id');
            $table->string('course_title_snapshot')->nullable()->after('course_code_snapshot');
            $table->integer('credit_units_snapshot')->nullable()->after('course_title_snapshot');
            $table->string('semester_snapshot')->nullable()->after('credit_units_snapshot');
            $table->integer('level_snapshot')->nullable()->after('semester_snapshot');
            $table->foreignId('course_version_id')->nullable()->after('level_snapshot')->constrained('course_versions')->nullOnDelete();

            $table->index(['course_code_snapshot', 'academic_session']);
        });
    }

    public function down(): void
    {
        Schema::table('registered_courses', function (Blueprint $table) {
            $table->dropForeign(['course_version_id']);
            $table->dropIndex(['course_code_snapshot', 'academic_session']);
            $table->dropColumn([
                'course_code_snapshot',
                'course_title_snapshot',
                'credit_units_snapshot',
                'semester_snapshot',
                'level_snapshot',
                'course_version_id',
            ]);
        });
    }
};
