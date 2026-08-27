<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_course_id')->constrained('department_courses')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->foreignUuid('lecturer_id')->constrained('users')->onDelete('cascade');
            $table->string('academic_session')->index();
            $table->string('semester')->default('first');
            $table->integer('assigned_units')->nullable();
            $table->timestamps();

            $table->unique(['department_course_id', 'lecturer_id', 'academic_session', 'semester'], 'unique_course_allocation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_allocations');
    }
};
