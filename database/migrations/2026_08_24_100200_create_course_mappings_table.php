<?php

use App\Models\Course;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('old_course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('new_course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('mapping_type')->default('equivalent'); // 'equivalent', 'replacement', 'merged', 'split'
            $table->string('effective_session'); // from session onwards
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['old_course_id', 'new_course_id', 'effective_session'], 'course_mapping_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_mappings');
    }
};
