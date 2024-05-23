<?php

use App\Models\Course;
use App\Models\Department;
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
        Schema::create('proposed_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id');
            $table->foreignIdFor(Department::class);
            $table->foreignIdFor(Course::class);
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposed_courses');
    }
};
