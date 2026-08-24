<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Duplicate Review Report\n";
echo "=======================\n\n";

// Check if backup table exists
if (!Schema::hasTable('registered_courses_duplicates')) {
    echo "No duplicate backup table found. Run the migration first.\n";
    exit;
}

// Get all backed up duplicates
$duplicates = DB::table('registered_courses_duplicates')
    ->orderBy('removed_at', 'desc')
    ->get();

echo "Total duplicates moved to backup: " . $duplicates->count() . "\n\n";

if ($duplicates->count() > 0) {
    echo "Sample of moved duplicates:\n";
    echo "==========================\n";
    
    foreach ($duplicates->take(10) as $dup) {
        echo "Original ID: {$dup->original_id}\n";
        echo "Student Detail ID: {$dup->academic_detail_id}\n";
        echo "Course ID: {$dup->department_course_id}\n";
        echo "Level ID: {$dup->student_level_id}\n";
        echo "Session: {$dup->academic_session}\n";
        echo "Removed at: {$dup->removed_at}\n";
        echo "Reason: {$dup->removal_reason}\n";
        echo "---\n";
    }
    
    echo "\nTo restore a specific duplicate, you can use:\n";
    echo "INSERT INTO registered_courses (id, academic_detail_id, department_course_id, student_level_id, academic_session, created_at, updated_at)\n";
    echo "SELECT original_id, academic_detail_id, department_course_id, student_level_id, academic_session, created_at, updated_at\n";
    echo "FROM registered_courses_duplicates WHERE original_id = [ID];\n";
    
    echo "\nTo restore all duplicates, run: php artisan migrate:rollback --step=1\n";
} else {
    echo "No duplicates were found and moved.\n";
}

echo "\nBackup table statistics:\n";
echo "=======================\n";
$stats = DB::table('registered_courses_duplicates')
    ->select('academic_session', DB::raw('COUNT(*) as count'))
    ->groupBy('academic_session')
    ->get();

foreach ($stats as $stat) {
    echo "Session {$stat->academic_session}: {$stat->count} duplicates\n";
}