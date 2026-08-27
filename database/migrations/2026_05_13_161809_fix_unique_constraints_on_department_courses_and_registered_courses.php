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
        // department_courses: a course can be offered in many departments,
        // but only once per department. Composite unique on (department_id, student_course_id).
        Schema::table('department_courses', function (Blueprint $table) {
            $table->unique(['department_id', 'student_course_id'], 'dept_course_unique');
        });

        // registered_courses: many students must be able to register the same
        // department_course. Composite unique on (academic_detail_id, department_course_id)
        // prevents a student from registering the same course twice in one academic detail.
        Schema::table('registered_courses', function (Blueprint $table) {
            $table->unique(['academic_detail_id', 'department_course_id'], 'reg_course_detail_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registered_courses', function (Blueprint $table) {
            $table->dropUnique('reg_course_detail_unique');
        });

        Schema::table('department_courses', function (Blueprint $table) {
            $table->dropUnique('dept_course_unique');
        });
    }
};
