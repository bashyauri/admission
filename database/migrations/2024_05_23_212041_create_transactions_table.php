<?php

use App\Models\User;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->foreignUuid('user_id');
            $table->string('amount');
            $table->string('date');
            $table->string('status')->nullable();
            $table->string('use_status')->nullable();
            $table->string('resource')->nullable();
            $table->string('RRR')->unique()->nullable();
            $table->integer('code')->default(0);
            $table->string('acad_session');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
