<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('capability'); // e.g., 'lecturer', 'exam_officer'
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('granted_at')->useCurrent();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignUuid('granted_by')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'capability', 'department_id']);
            $table->index(['user_id', 'is_active']);
            $table->index('capability');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_capabilities');
    }
};
