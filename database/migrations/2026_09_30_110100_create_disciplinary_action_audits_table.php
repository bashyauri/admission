<?php

use App\Models\DisciplinaryAction;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplinary_action_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DisciplinaryAction::class)->constrained()->restrictOnDelete();
            $table->foreignUuid('student_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('resolution_ref')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['student_id', 'occurred_at'], 'disciplinary_audit_student_time_idx');
            $table->index(['disciplinary_action_id', 'occurred_at'], 'disciplinary_audit_action_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_action_audits');
    }
};
