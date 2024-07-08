<?php

use App\Models\Lga;
use App\Models\Programme;
use App\Models\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Programme::class);
            $table->string('picture')->nullable();
            $table->string('surname');
            $table->string('firstname');
            $table->string('m_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('birthday')->nullable();
            $table->foreignIdFor(State::class)->nullable();
            $table->foreignIdFor(Lga::class)->nullable();
            $table->string('home_town')->nullable();
            $table->string('nationality')->nullable();
            $table->string('home_address')->nullable();
            $table->string('cor_address')->nullable();
            $table->string('kin_name')->nullable();
            $table->string('kin_address')->nullable();
            $table->string('kin_phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('role', ['applicant', 'student', 'graduate', 'hod', 'admin'])->default('applicant')->nullable();
            $table->string('password');
            $table->string('vpassword');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
