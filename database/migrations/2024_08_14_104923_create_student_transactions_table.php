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
        Schema::create('student_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->foreignUuid('user_id')->constrained();
            $table->foreignId('student_levels_id')->constrained();
            $table->string('date');
            $table->string('status')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('resource')->nullable();
            $table->string('RRR')->unique()->nullable();
            $table->string('acad_session');
            $table->boolean('paid')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_transactions');
    }
};
