<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\Report\UtmeService;

$service = app(UtmeService::class);
$students = $service->getUndergraduateStudentsWithPaymentStatus();

echo "Total students with payment status != '00': " . $students->count() . PHP_EOL;
echo PHP_EOL;

foreach ($students->take(5) as $student) {
    echo "Name: " . $student->surname . ", " . $student->firstname . PHP_EOL;
    echo "Matric No: " . ($student->matric_no ?? 'N/A') . PHP_EOL;
    echo "Department: " . ($student->academic_detail->department->name ?? 'N/A') . PHP_EOL;
    echo "Level: " . ($student->academic_detail->student_level->level ?? 'N/A') . PHP_EOL;
    echo "RRR: " . ($student->RRR ?? 'N/A') . PHP_EOL;
    echo "Payment Status: " . ($student->payment_status ?? 'NULL') . PHP_EOL;
    echo "---" . PHP_EOL;
}
