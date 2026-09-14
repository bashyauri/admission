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
        Schema::create('graduation_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(GraduationList::class)->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(AcademicDetail::class)->constrained()->cascadeOnDelete();
            $table->string('matric_no');
            $table->string('full_name');
            $table->string('programme');
            $table->string('department');
            $table->decimal('final_cgpa', 5, 2);
            $table->string('class_of_degree');
            $table->integer('rank')->nullable();
            $table->boolean('is_present')->default(false);
            $table->timestamps();

            $table->unique(['graduation_list_id', 'user_id'], 'grad_list_user_unique');
            $table->index(['graduation_list_id', 'class_of_degree']);
            $table->index('matric_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduation_list_items');
    }
};
