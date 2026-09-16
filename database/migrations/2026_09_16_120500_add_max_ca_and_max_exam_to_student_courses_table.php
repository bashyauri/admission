<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            if (!Schema::hasColumn('student_courses', 'max_ca')) {
                $table->unsignedSmallInteger('max_ca')->default(40)->after('semester');
            }
            if (!Schema::hasColumn('student_courses', 'max_exam')) {
                $table->unsignedSmallInteger('max_exam')->default(60)->after('max_ca');
            }
        });

        // Set practical science courses (e.g. PHY107, PHY108, CHM107, CHM108, BIO107, BIO108) to 60 CA / 40 Exam
        $practicalCodes = ['PHY107', 'PHY108', 'CHM107', 'CHM108', 'BIO107', 'BIO108'];
        DB::table('student_courses')
            ->whereIn('code', $practicalCodes)
            ->update([
                'max_ca' => 60,
                'max_exam' => 40,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_courses', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('student_courses', 'max_exam')) {
                $columnsToDrop[] = 'max_exam';
            }
            if (Schema::hasColumn('student_courses', 'max_ca')) {
                $columnsToDrop[] = 'max_ca';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
