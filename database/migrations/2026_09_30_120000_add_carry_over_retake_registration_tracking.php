<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carry_over_courses', function (Blueprint $table) {
            $table->foreignId('retake_registered_course_id')
                ->nullable()
                ->after('registered_course_id')
                ->constrained('registered_courses')
                ->nullOnDelete();
            $table->string('registration_status')->default('pending')->after('auto_registered_at');
            $table->text('review_reason')->nullable()->after('registration_status');
            $table->foreignId('approved_department_course_id')
                ->nullable()
                ->after('review_reason')
                ->constrained('department_courses')
                ->nullOnDelete();
            $table->foreignUuid('reviewed_by')->nullable()->after('approved_department_course_id')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_note')->nullable()->after('reviewed_at');
        });

        // Retakes must be registerable in a later session. Remove legacy
        // uniqueness rules that prohibited a student repeating a course at all.
        $legacyColumns = ['academic_detail_id', 'department_course_id'];
        foreach (Schema::getIndexes('registered_courses') as $index) {
            if (($index['unique'] ?? false) && ($index['columns'] ?? []) === $legacyColumns) {
                Schema::table('registered_courses', function (Blueprint $table) use ($index) {
                    $table->dropUnique($index['name']);
                });
            }
        }

        $sessionColumns = ['academic_detail_id', 'department_course_id', 'academic_session'];
        $hasSessionUnique = collect(Schema::getIndexes('registered_courses'))->contains(
            fn (array $index): bool => ($index['unique'] ?? false)
                && ($index['columns'] ?? []) === $sessionColumns
        );

        if (!$hasSessionUnique) {
            Schema::table('registered_courses', function (Blueprint $table) {
                $table->unique(
                    ['academic_detail_id', 'department_course_id', 'academic_session'],
                    'reg_course_session_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('carry_over_courses', function (Blueprint $table) {
            $table->dropForeign(['retake_registered_course_id']);
            $table->dropForeign(['approved_department_course_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'retake_registered_course_id',
                'registration_status',
                'review_reason',
                'approved_department_course_id',
                'reviewed_by',
                'reviewed_at',
                'review_note',
            ]);
        });

        // Keep the session-scoped unique constraint in place on rollback: data
        // may already contain legitimate retakes from multiple sessions.
    }
};
