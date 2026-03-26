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
        Schema::create('applicant_deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('deleted_user_ids');
            $table->json('deletion_summary'); // counts per table
            $table->string('export_file_path')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applicant_deletion_logs');
    }
};
