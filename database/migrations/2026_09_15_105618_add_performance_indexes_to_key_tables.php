<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes to tables most queried in Livewire render cycles.
     *
     * Targets:
     *  - user_capabilities: capability + is_active aggregate queries
     *  - results: per-course / session / semester / status review queries
     *  - coordinators: course cohort lookups
     *  - academic_details: session population + student count queries
     */
    public function up(): void
    {
        // Speed up the selectRaw aggregate and per-capability filters
        Schema::table('user_capabilities', function (Blueprint $table) {
            $table->index(['capability', 'is_active'], 'idx_ucap_capability_active');
        });

        // Speed up CoordinatorResultReview / ExamOfficerResultReview result queries
        // Schema::table('results', function (Blueprint $table) {
        //     $table->index(
        //         ['department_course_id', 'academic_session', 'semester', 'status'],
        //         'idx_results_dept_session_semester_status'
        //     );
        // });

        // Speed up coordinator cohort existence checks
        Schema::table('coordinators', function (Blueprint $table) {
            $table->index(
                ['course_id', 'student_level_id', 'academic_session'],
                'idx_coordinators_cohort_lookup'
            );
        });

        // Speed up student count queries and session-building plucks
        Schema::table('academic_details', function (Blueprint $table) {
            $table->index(
                ['admission_session', 'course_id', 'student_level_id'],
                'idx_adetails_session_course_level'
            );
        });
    }

    public function down(): void
    {
        Schema::table('user_capabilities', function (Blueprint $table) {
            $table->dropIndex('idx_ucap_capability_active');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('idx_results_dept_session_semester_status');
        });

        Schema::table('coordinators', function (Blueprint $table) {
            $table->dropIndex('idx_coordinators_cohort_lookup');
        });

        Schema::table('academic_details', function (Blueprint $table) {
            $table->dropIndex('idx_adetails_session_course_level');
        });
    }
};
