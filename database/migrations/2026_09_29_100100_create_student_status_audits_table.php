<?php

use App\Models\StudentStatusRecord;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_status_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(StudentStatusRecord::class)->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('old_decision')->nullable();
            $table->string('new_decision')->nullable();
            $table->text('reason')->nullable();
            $table->string('senate_reference')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['student_id', 'occurred_at']);
            $table->index(['actor_id', 'occurred_at']);
            $table->index(['action', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_status_audits');
    }
};
