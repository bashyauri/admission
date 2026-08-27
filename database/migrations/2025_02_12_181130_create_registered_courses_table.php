<?php

use App\Models\AcademicDetail;
use App\Models\DepartmentCourse;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
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
        Schema::create('registered_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AcademicDetail::class)->constrained();
            $table->foreignIdFor(DepartmentCourse::class)->constrained();
            $table->foreignIdFor(StudentLevel::class)->constrained();
            $table->string('units');
            $table->string('academic_session');
            $table->timestamps();
            
            // Add unique constraint with custom name to avoid 64-character limit
            $table->unique(['academic_detail_id', 'department_course_id'], 'reg_course_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registered_courses');
    }
};