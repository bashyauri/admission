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
        Schema::create('graduation_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Senate Approved Graduating List');
            $table->string('academic_session');
            $table->string('ceremony_date')->nullable();
            $table->string('venue')->nullable();
            $table->boolean('is_published')->default(false);
            $table->foreignUuid('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['academic_session', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduation_lists');
    }
};
