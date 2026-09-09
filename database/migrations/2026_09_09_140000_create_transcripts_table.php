<?php

use App\Models\User;
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
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('request_number')->unique();
            $table->string('verification_code')->nullable()->unique();
            $table->string('destination')->nullable();
            $table->string('purpose')->nullable();
            $table->enum('status', ['pending', 'processing', 'ready', 'sent', 'collected'])->default('pending');
            $table->decimal('fee', 10, 2)->default(0);
            $table->boolean('fee_paid')->default(false);
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->foreignUuid('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
