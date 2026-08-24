<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Count duplicates
$duplicates = DB::table('registered_courses')
    ->select('academic_detail_id', 'department_course_id', 'academic_session', DB::raw('COUNT(*) as count'))
    ->groupBy('academic_detail_id', 'department_course_id', 'academic_session')
    ->havingRaw('COUNT(*) > 1')
    ->get();

$totalDuplicates = $duplicates->sum('count') - $duplicates->count();

echo "Duplicate Analysis:\n";
echo "==================\n";
echo "Number of duplicate sets: " . $duplicates->count() . "\n";
echo "Total duplicate rows to be removed: " . $totalDuplicates . "\n";
echo "Total registrations to be kept: " . $duplicates->count() . "\n";
echo "\n";

if ($duplicates->count() > 0) {
    echo "Sample duplicates:\n";
    echo "================\n";
    foreach ($duplicates->take(5) as $dup) {
        echo "Student ID: {$dup->academic_detail_id}, Course ID: {$dup->department_course_id}, Session: {$dup->academic_session}, Count: {$dup->count}\n";
    }
}