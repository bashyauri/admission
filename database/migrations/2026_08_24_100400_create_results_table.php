<?php

use App\Models\AcademicDetail;
use App\Models\DepartmentCourse;
use App\Models\RegisteredCourse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(RegisteredCourse::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(DepartmentCourse::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(AcademicDetail::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('course_version_id')->nullable()->constrained('course_versions')->nullOnDelete();
            
            // Snapshot attributes
            $table->string('course_code_snapshot')->nullable();
            $table->string('course_title_snapshot')->nullable();
            $table->integer('credit_units_snapshot')->nullable();
            $table->string('semester_snapshot')->nullable();
            $table->integer('level_snapshot')->nullable();

            // Academic period
            $table->string('semester'); // 'first' or 'second'
            $table->string('academic_session'); // '2024-2025'

            // Scores & Grades (NUC standard)
            $table->decimal('ca_score', 5, 2)->nullable(); // 0-40
            $table->decimal('exam_score', 5, 2)->nullable(); // 0-60
            $table->decimal('total_score', 5, 2)->nullable(); // 0-100
            $table->string('grade', 5)->nullable(); // A, B, C, D, F
            $table->integer('grade_point')->nullable(); // 5, 4, 3, 2, 0
            $table->integer('credit_units')->default(0);
            $table->integer('grade_point_total')->nullable(); // grade_point * credit_units

            // Status & Approvals
            $table->string('status')->default('pending'); // pending, submitted, hod_approved, exam_officer_approved, released
            $table->foreignUuid('lecturer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('hod_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hod_approved_at')->nullable();
            $table->foreignUuid('exam_officer_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('exam_officer_approved_at')->nullable();

            $table->text('remarks')->nullable();
            $table->boolean('is_repeated')->default(false);
            $table->foreignId('original_result_id')->nullable()->constrained('results')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'registered_course_id', 'academic_session', 'semester'], 'result_unique_entry');
            $table->index(['user_id', 'academic_session', 'semester']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
