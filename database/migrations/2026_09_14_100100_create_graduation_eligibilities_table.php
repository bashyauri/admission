<?php

use App\Models\AcademicDetail;
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
        Schema::create('graduation_eligibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(AcademicDetail::class)->constrained()->cascadeOnDelete();
            $table->string('academic_session');
            $table->decimal('final_cgpa', 5, 2)->default(0.00);
            $table->string('class_of_degree')->nullable();
            $table->integer('total_units_earned')->default(0);
            $table->integer('total_units_required')->default(0);
            $table->boolean('meets_requirements')->default(false);
            $table->boolean('siwes_completed')->default(false);
            $table->boolean('general_studies_completed')->default(false);
            $table->boolean('entrepreneurship_completed')->default(false);
            $table->boolean('is_cleared')->default(false);
            $table->foreignUuid('cleared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cleared_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'academic_session'], 'ug_grad_eligibility_user_session_unique');
            $table->index(['academic_session', 'is_cleared']);
            $table->index(['meets_requirements', 'is_cleared']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduation_eligibilities');
    }
};
