<?php

use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('senate_grade_corrections', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Result::class)->constrained()->restrictOnDelete();
            $table->string('senate_reference', 100);
            $table->date('senate_decision_date');
            $table->string('senate_minutes_reference', 255);
            $table->string('evidence_path');
            $table->string('verification_method', 255);
            $table->foreignUuid('verified_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('verified_at');
            $table->string('correction_type', 40);
            $table->decimal('adjustment_amount', 6, 2)->nullable();
            $table->decimal('original_ca', 5, 2);
            $table->decimal('original_exam', 5, 2);
            $table->decimal('original_total', 5, 2);
            $table->string('original_grade', 5)->nullable();
            $table->unsignedTinyInteger('original_grade_point')->nullable();
            $table->decimal('corrected_ca', 5, 2);
            $table->decimal('corrected_exam', 5, 2);
            $table->decimal('corrected_total', 5, 2);
            $table->string('corrected_grade', 5);
            $table->unsignedTinyInteger('corrected_grade_point');
            $table->text('reason');
            $table->json('carry_over_before')->nullable();
            $table->json('carry_over_after')->nullable();
            $table->foreignUuid('implemented_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('implemented_at');
            $table->timestamps();

            $table->unique(['senate_reference', 'result_id'], 'senate_correction_reference_result_unique');
            $table->index(['result_id', 'implemented_at'], 'senate_correction_result_history_idx');
            $table->index(['senate_reference', 'senate_decision_date'], 'senate_correction_reference_date_idx');
            $table->index('implemented_by', 'senate_correction_implementer_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senate_grade_corrections');
    }
};
