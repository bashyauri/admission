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
        Schema::create('academic_progression_records', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(AcademicDetail::class)->constrained('academic_details')->cascadeOnDelete();
            $table->string('academic_session');
            $table->integer('semester');
            $table->string('level')->nullable();
            $table->decimal('cgpa', 5, 2)->default(0.00);
            $table->string('standing')->default('GOOD_STANDING');
            $table->boolean('withdrawal_recommended')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'academic_session', 'semester'], 'acad_prog_user_sess_sem_idx');
            $table->index(['academic_detail_id', 'academic_session'], 'acad_prog_detail_sess_idx');
            $table->index(['standing', 'withdrawal_recommended'], 'acad_prog_standing_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_progression_records');
    }
};
