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
            if (!Schema::hasColumn('coordinators', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            }
            
            $table->unique(['department_id', 'student_level_id', 'academic_session'], 'dept_cohort_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinators', function (Blueprint $table) {
            $table->dropUnique('dept_cohort_unique');
        });
    }
};