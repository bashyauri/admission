<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking user capabilities:\n";
echo "=========================\n";

$caps = \App\Models\UserCapability::with('user')->get();
echo "Total capabilities: " . $caps->count() . "\n\n";

foreach ($caps as $cap) {
    $user = $cap->user;
    echo "User: " . ($user ? $user->firstname . ' ' . $user->surname : 'Unknown') . "\n";
    echo "User ID: " . $cap->user_id . "\n";
    echo "Primary Role: " . ($user ? $user->role : 'Unknown') . "\n";
    echo "Capability: " . $cap->capability . "\n";
    echo "Active: " . ($cap->is_active ? 'Yes' : 'No') . "\n";
    echo "Can Act As Coordinator: " . ($user && $user->canActAsCoordinator() ? 'Yes' : 'No') . "\n";
    echo "Can Act As Lecturer: " . ($user && $user->canActAsLecturer() ? 'Yes' : 'No') . "\n";
    echo "------------------------\n";
}