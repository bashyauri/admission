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
        Schema::create('student_status_records', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(AcademicDetail::class)->constrained('academic_details')->cascadeOnDelete();
            $table->string('status');
            $table->string('status_type');
            $table->string('reason_code')->nullable();
            $table->text('reason')->nullable();
            $table->string('academic_session');
            $table->integer('semester')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('senate_reference')->nullable();
            $table->date('senate_decision_date')->nullable();
            $table->string('senate_decision')->nullable();
            $table->boolean('reinstatement_eligible')->default(false);
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'stud_stat_user_status_idx');
            $table->index(['academic_detail_id', 'academic_session'], 'stud_stat_detail_sess_idx');
            $table->index(['status', 'senate_decision'], 'stud_stat_status_decision_idx');
            $table->index('effective_date', 'stud_stat_effective_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_status_records');
    }
};
