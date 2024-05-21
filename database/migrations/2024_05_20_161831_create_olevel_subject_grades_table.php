<?php

use App\Models\Olevel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('olevel_subject_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id');
            $table->string('subject_name');
            $table->string('grade');
            $table->string('exam_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olevel_subject_grades');
    }
};
