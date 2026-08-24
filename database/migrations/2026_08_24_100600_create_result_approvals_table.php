<?php

use App\Models\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Department::class)->nullable()->constrained()->nullOnDelete();
            $table->string('academic_session');
            $table->string('semester');
            $table->string('approval_level'); // 'lecturer', 'hod', 'exam_officer', 'senate'
            $table->foreignUuid('approved_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('approved_at')->useCurrent();
            $table->string('status')->default('approved'); // 'pending', 'approved', 'rejected'
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->index(['academic_session', 'semester', 'approval_level']);
            $table->index('approved_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_approvals');
    }
};
