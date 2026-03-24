<?php

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
        Schema::create('course_drop_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('academic_session');
            $table->json('course_codes');
            $table->json('filters')->nullable();
            $table->unsignedInteger('matched_count')->default(0);
            $table->unsignedInteger('dropped_count')->default(0);
            $table->string('action_type');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['action_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_drop_audits');
    }
};
