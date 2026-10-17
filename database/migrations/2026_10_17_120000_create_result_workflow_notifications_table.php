<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_workflow_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('event_key')->unique();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 60);
            $table->string('title');
            $table->text('message');
            $table->string('action_url', 2048)->nullable();
            $table->json('context')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('email_status', 20)->default('queued');
            $table->unsignedSmallInteger('email_attempts')->default(0);
            $table->timestamp('email_sent_at')->nullable();
            $table->text('email_failure')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'created_at'], 'rw_notif_user_read_created_idx');
            $table->index(['email_status', 'created_at'], 'rw_notif_email_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_workflow_notifications');
    }
};
