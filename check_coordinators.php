<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking coordinator assignments:\n";
echo "================================\n\n";

$coordinators = \App\Models\Coordinator::with(['user', 'course', 'studentLevel'])->get();
echo "Total coordinators: " . $coordinators->count() . "\n\n";

// Filter for 2025/2026 session and level 200
$targetSession = '2025/2026';
$targetLevel = 200;

echo "Looking for coordinators for session: $targetSession, level: $targetLevel\n";
echo "---------------------------------------------------\n";

$matchingCoordinators = $coordinators->filter(function ($coord) use ($targetSession, $targetLevel) {
    return $coord->academic_session === $targetSession && $coord->student_level_id == $targetLevel;
});

if ($matchingCoordinators->isEmpty()) {
    echo "❌ No coordinators found for session: $targetSession, level: $targetLevel\n\n";
} else {
    echo "✅ Found " . $matchingCoordinators->count() . " coordinator(s) for session: $targetSession, level: $targetLevel\n\n";
    
    foreach ($matchingCoordinators as $coord) {
        echo "Coordinator: " . ($coord->user ? $coord->user->firstname . ' ' . $coord->user->surname : 'Unknown') . "\n";
        echo "Course: " . ($coord->course ? $coord->course->code . ' - ' . $coord->course->title : 'Unknown') . "\n";
        echo "Level: " . ($coord->studentLevel ? $coord->studentLevel->name : 'Unknown') . "\n";
        echo "Session: " . $coord->academic_session . "\n";
        echo "------------------------\n";
    }
}

echo "\n\nAll current coordinator assignments:\n";
echo "=================================\n";

foreach ($coordinators as $coord) {
    echo "Coordinator: " . ($coord->user ? $coord->user->firstname . ' ' . $coord->user->surname : 'Unknown') . "\n";
    echo "Course: " . ($coord->course ? $coord->course->code . ' - ' . $coord->course->title : 'Unknown') . "\n";
    echo "Level: " . ($coord->studentLevel ? $coord->studentLevel->name : 'Unknown') . "\n";
    echo "Session: " . $coord->academic_session . "\n";
    echo "------------------------\n";
}