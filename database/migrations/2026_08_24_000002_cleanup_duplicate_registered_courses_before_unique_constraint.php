<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a backup table for duplicates before removal
        Schema::create('registered_courses_duplicates', function (Blueprint $table) {
    $table->id();

    $table->foreignId('academic_detail_id')
        ->constrained()
        ->onDelete('cascade');

    $table->foreignId('department_course_id')
        ->constrained()
        ->onDelete('cascade');

    $table->foreignId('student_level_id')
        ->nullable()
        ->constrained()
        ->onDelete('set null');

    $table->string('academic_session', 50);

    $table->timestamp('removed_at')->useCurrent();

    $table->text('removal_reason')->nullable();

    $table->integer('original_id')->nullable();

    $table->timestamps();

    $table->index(
        ['academic_detail_id', 'department_course_id', 'academic_session'],
        'rc_duplicates_lookup_idx'
    );
});
        
        // First, identify and handle duplicate registrations
        // Find duplicates of (academic_detail_id, department_course_id, academic_session)
        $duplicates = DB::table('registered_courses')
            ->select('academic_detail_id', 'department_course_id', 'academic_session', DB::raw('MIN(id) as id_to_keep'))
            ->groupBy('academic_detail_id', 'department_course_id', 'academic_session')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        
        // Move duplicates to backup table instead of deleting
        foreach ($duplicates as $duplicate) {
            $duplicateRows = DB::table('registered_courses')
                ->where('academic_detail_id', $duplicate->academic_detail_id)
                ->where('department_course_id', $duplicate->department_course_id)
                ->where('academic_session', $duplicate->academic_session)
                ->where('id', '!=', $duplicate->id_to_keep)
                ->get();
            
            foreach ($duplicateRows as $row) {
                // Insert into backup table
                DB::table('registered_courses_duplicates')->insert([
                    'academic_detail_id' => $row->academic_detail_id,
                    'department_course_id' => $row->department_course_id,
                    'student_level_id' => $row->student_level_id,
                    'academic_session' => $row->academic_session,
                    'original_id' => $row->id,
                    'removal_reason' => 'Duplicate registration cleanup - same student, course, and session',
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
                
                // Then delete from main table
                DB::table('registered_courses')->where('id', $row->id)->delete();
            }
        }
        
        // Now it's safe to add the unique constraint
        Schema::table('registered_courses', function (Blueprint $table) {
            $table->unique(['academic_detail_id', 'department_course_id', 'academic_session'], 'reg_course_session_unique');
        });
        
        // Add other performance indexes
        Schema::table('registered_courses', function (Blueprint $table) {
            $table->index('academic_session', 'idx_academic_session');
            $table->index(['academic_detail_id', 'academic_session'], 'idx_student_session');
            $table->index(['department_course_id', 'academic_session'], 'idx_course_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore duplicates from backup table
        $duplicates = DB::table('registered_courses_duplicates')->get();
        
        foreach ($duplicates as $duplicate) {
            DB::table('registered_courses')->insert([
                'id' => $duplicate->original_id,
                'academic_detail_id' => $duplicate->academic_detail_id,
                'department_course_id' => $duplicate->department_course_id,
                'student_level_id' => $duplicate->student_level_id,
                'academic_session' => $duplicate->academic_session,
                'created_at' => $duplicate->created_at,
                'updated_at' => $duplicate->updated_at,
            ]);
        }
        
        // Drop the constraints and indexes
        Schema::table('registered_courses', function (Blueprint $table) {
            $table->dropUnique('reg_course_session_unique');
            $table->dropIndex('idx_academic_session');
            $table->dropIndex('idx_student_session');
            $table->dropIndex('idx_course_session');
        });
        
        // Drop the backup table
        Schema::dropIfExists('registered_courses_duplicates');
    }
};