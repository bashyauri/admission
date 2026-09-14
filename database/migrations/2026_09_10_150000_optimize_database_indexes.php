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
        // 1. Optimize department_courses table (was missing all secondary indexes)
        Schema::table('department_courses', function (Blueprint $table) {
            $table->index('department_id', 'department_courses_department_id_index');
            $table->index('student_course_id', 'department_courses_student_course_id_index');
            $table->unique(['department_id', 'student_course_id'], 'department_courses_dept_course_unique');
        });

        // 2. Optimize results table for Exam Officer audits and Senate Broadsheet aggregations
        Schema::table('results', function (Blueprint $table) {
            $table->index(
                ['department_course_id', 'academic_session', 'semester', 'status'],
                'results_course_period_status_index'
            );
            $table->index(
                ['academic_session', 'semester', 'status'],
                'results_session_semester_status_index'
            );
        });

        // 3. Optimize academic_details table for departmental cohort filters & degree classification
        Schema::table('academic_details', function (Blueprint $table) {
            $table->index('department_id', 'academic_details_department_id_index');
            $table->index('programme_id', 'academic_details_programme_id_index');
            $table->index(
                ['department_id', 'admission_session'],
                'academic_details_dept_admission_session_index'
            );
        });

        // 4. Optimize users table for role & programme lookups
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
            $table->index('programme_id', 'users_programme_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_programme_id_index');
        });

        Schema::table('academic_details', function (Blueprint $table) {
            $table->dropIndex('academic_details_department_id_index');
            $table->dropIndex('academic_details_programme_id_index');
            $table->dropIndex('academic_details_dept_admission_session_index');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('results_course_period_status_index');
            $table->dropIndex('results_session_semester_status_index');
        });

        Schema::table('department_courses', function (Blueprint $table) {
            $table->dropUnique('department_courses_dept_course_unique');
            $table->dropIndex('department_courses_department_id_index');
            $table->dropIndex('department_courses_student_course_id_index');
        });
    }
};
