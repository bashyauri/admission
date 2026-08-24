<?php

use App\Models\Course;
use App\Models\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Course::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Department::class)->nullable()->constrained()->nullOnDelete();
            $table->string('course_code');
            $table->string('course_title');
            $table->integer('credit_units');
            $table->string('semester')->default('first'); // 'first' or 'second'
            $table->integer('level')->default(100); // 100, 200, 300, 400, etc.
            $table->boolean('is_compulsory')->default(true);
            $table->boolean('is_prerequisite')->default(false);
            $table->string('academic_session'); // '2024-2025'
            $table->boolean('is_active')->default(true);
            $table->timestamp('effective_date')->useCurrent();
            $table->timestamp('expiry_date')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['course_code', 'academic_session']);
            $table->index(['course_code', 'is_active']);
            $table->index('academic_session');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_versions');
    }
};
