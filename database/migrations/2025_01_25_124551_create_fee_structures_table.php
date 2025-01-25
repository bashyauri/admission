<?php

use App\Models\Programme;
use App\Models\Department;
use App\Models\StudentLevel;
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
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Department::class)->constrained();
            $table->foreignIdFor(Programme::class)->constrained();
            $table->foreignIdFor(StudentLevel::class)->constrained();
            $table->decimal('fee_amount', 10, 2); // 10 digits total, 2 decimal places
            $table->boolean('is_fresh_fee')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};
