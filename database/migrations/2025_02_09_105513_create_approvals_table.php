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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AcademicDetail::class)->constrained()->cascadeOnDelete();
            $table->foreignId('coordinator_id')->constrained()->onDelete('cascade');
            $table->string('pin')->nullable()->unique();
            $table->boolean('is_used')->default(false);
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->timestamp('approval_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
