<?php

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
        Schema::table('results', function (Blueprint $table) {
            // Add coordinator_id (bigint FK to coordinators table) if not already present
            if (!Schema::hasColumn('results', 'coordinator_id')) {
                $table->foreignId('coordinator_id')->nullable()->after('lecturer_id')->constrained()->nullOnDelete();
            }

            // coordinator_approved_by must be char(36) to match users.id (UUID)
            // If it already exists as wrong type from a partial migration, drop and recreate
            if (Schema::hasColumn('results', 'coordinator_approved_by')) {
                $table->dropColumn('coordinator_approved_by');
            }
        });

        Schema::table('results', function (Blueprint $table) {
            // Recreate with correct UUID type to match users.id
            $table->foreignUuid('coordinator_approved_by')->nullable()->after('coordinator_id')->constrained('users')->nullOnDelete();

            if (!Schema::hasColumn('results', 'coordinator_approved_at')) {
                $table->timestamp('coordinator_approved_at')->nullable()->after('coordinator_approved_by');
            }

            // Add index for coordinator-based queries (only if not already present)
            $indexExists = collect(\Illuminate\Support\Facades\DB::select(
                "SHOW INDEX FROM results WHERE Key_name = 'coordinator_status_index'"
            ))->isNotEmpty();
            if (!$indexExists) {
                $table->index(['coordinator_id', 'status'], 'coordinator_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex('coordinator_status_index');
            $table->dropForeign(['coordinator_approved_by']);
            $table->dropForeign(['coordinator_id']);
            $table->dropColumn(['coordinator_id', 'coordinator_approved_by', 'coordinator_approved_at']);
        });
    }
};