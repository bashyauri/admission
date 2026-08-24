<?php

use App\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_change_history', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Course::class)->constrained()->cascadeOnDelete();
            $table->foreignId('previous_version_id')->nullable()->constrained('course_versions')->nullOnDelete();
            $table->foreignId('new_version_id')->nullable()->constrained('course_versions')->nullOnDelete();
            $table->string('change_type'); // 'name_change', 'unit_change', 'code_change', 'deletion', 'replacement'
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('academic_session');
            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('change_date')->useCurrent();
            $table->timestamps();

            $table->index(['course_id', 'academic_session']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_change_history');
    }
};
