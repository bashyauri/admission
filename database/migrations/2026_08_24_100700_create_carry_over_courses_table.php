<?php

use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carry_over_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(RegisteredCourse::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(DepartmentCourse::class)->constrained()->cascadeOnDelete();
            $table->string('failed_session');
            $table->string('failed_semester');
            $table->decimal('failed_score', 5, 2)->default(0);
            $table->string('failed_grade', 5)->default('F');
            $table->string('retake_session')->nullable();
            $table->string('retake_semester')->nullable();
            $table->boolean('is_cleared')->default(false);
            $table->timestamp('cleared_at')->nullable();
            $table->foreignId('cleared_result_id')->nullable()->constrained('results')->nullOnDelete();
            $table->boolean('auto_registered')->default(false);
            $table->timestamp('auto_registered_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'department_course_id', 'failed_session'], 'carry_over_unique');
            $table->index(['user_id', 'is_cleared']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carry_over_courses');
    }
};
