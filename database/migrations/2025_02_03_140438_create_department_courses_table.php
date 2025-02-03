<?php

use App\Models\Department;
use App\Models\StudentCourse;
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
        Schema::create('department_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Department::class);
            $table->foreignIdFor(StudentCourse::class);
            $table->integer('units');
            $table->unique(['student_course_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_courses');
    }
};
