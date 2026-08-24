<?php

use App\Models\AcademicDetail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_gpa_records', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(AcademicDetail::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('semester'); // 'first' or 'second'
            $table->string('academic_session'); // '2024-2025'
            
            // Semester calculations
            $table->decimal('semester_gpa', 5, 2)->nullable();
            $table->integer('total_credit_units')->default(0);
            $table->integer('total_grade_points')->default(0);
            
            // Cumulative calculations
            $table->decimal('cumulative_gpa', 5, 2)->nullable();
            $table->integer('cumulative_credit_units')->default(0);
            $table->integer('cumulative_grade_points')->default(0);
            
            // Degree class according to NUC thresholds
            $table->string('class_of_degree')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'academic_session', 'semester'], 'gpa_record_unique');
            $table->index(['user_id', 'academic_session']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_gpa_records');
    }
};
