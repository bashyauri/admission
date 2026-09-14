<?php

use App\Models\AcademicDetail;
use App\Models\GraduationList;
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
        Schema::create('degree_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(AcademicDetail::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(GraduationList::class)->nullable()->constrained()->nullOnDelete();
            $table->string('certificate_number')->unique();
            $table->string('certificate_type')->default('bachelor'); // 'bachelor', 'diploma'
            $table->string('class_of_degree');
            $table->date('issue_date');
            $table->string('file_path')->nullable();
            $table->boolean('is_printed')->default(false);
            $table->boolean('is_collected')->default(false);
            $table->timestamp('collected_at')->nullable();
            $table->foreignUuid('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_name')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'certificate_number']);
            $table->index(['is_collected', 'issue_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('degree_certificates');
    }
};
