<?php

use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\StudentStatusRecord;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(AcademicDetail::class)->constrained('academic_details')->cascadeOnDelete();
            $table->enum('sanction_type', ['course_cancellation', 'repeat_session', 'suspension', 'expulsion']);
            $table->foreignIdFor(Course::class)->nullable()->constrained('courses')->nullOnDelete();
            $table->string('academic_session');
            $table->string('semester')->nullable();
            $table->string('senate_ref_no');
            $table->date('verdict_date');
            $table->string('effective_session');
            $table->string('resumption_session')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_appealed')->default(false);
            $table->string('appeal_status')->nullable();
            $table->foreignUuid('sanctioned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(StudentStatusRecord::class)->nullable()->constrained('student_status_records')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active'], 'disciplinary_user_active_idx');
            $table->index(['academic_detail_id', 'effective_session'], 'disciplinary_detail_effective_idx');
            $table->index(['sanction_type', 'is_active'], 'disciplinary_type_active_idx');
            $table->index('senate_ref_no', 'disciplinary_senate_ref_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_actions');
    }
};
