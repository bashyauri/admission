<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Services\Report\FubkStudentReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FubkReportExportController extends Controller
{
    private const MAX_PDF_ROWS = 1200;

    private const COLUMN_OPTIONS = [
        'full_name' => 'Full Name',
        'gender' => 'Gender',
        'email' => 'Email',
        'phone' => 'Phone',
        'matric_no' => 'Matric Number',
        'jamb_no' => 'JAMB Number',
        'nationality' => 'Nationality',
        'state' => 'State',
        'lga' => 'LGA',
        'department_name' => 'Department',
        'programme_name' => 'Programme',
        'admission_session' => 'Admission Session',
        'registered_courses' => 'Registered Courses',
    ];

    private const COURSE_REGISTRATION_COLUMNS = [
        'student_name' => 'Student Name',
        'matric_no' => 'Matric Number',
        'department_name' => 'Department',
        'programme_name' => 'Programme',
        'academic_session' => 'Academic Session',
        'level_name' => 'Level',
        'semester' => 'Semester',
        'course_code' => 'Course Code',
        'course_title' => 'Course Title',
        'credit_units' => 'Credit Units',
    ];

    public function __invoke(Request $request, FubkStudentReportService $reportService)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:fresh,returning,all'],
            'format' => ['nullable', 'in:csv,pdf'],
            'dataset' => ['nullable', 'in:biodata,course_registration'],
            'session' => ['required', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'programme_id' => ['nullable', 'integer', 'exists:programmes,id'],
            'columns' => ['nullable', 'string', 'max:2000'],
        ]);

        $dataset = (string) ($validated['dataset'] ?? 'biodata');

        $allowedColumns = array_keys(self::COLUMN_OPTIONS);
        $selectedColumns = collect(explode(',', (string) ($validated['columns'] ?? '')))
            ->map(static fn(string $column): string => trim($column))
            ->filter(static fn(string $column): bool => in_array($column, $allowedColumns, true))
            ->unique()
            ->values()
            ->all();

        if ($selectedColumns === []) {
            $selectedColumns = ['full_name'];
        }

        if ($dataset === 'course_registration') {
            $rows = $reportService->exportCourseRegistrations(
                (string) $validated['type'],
                (string) $validated['session'],
                (string) ($validated['search'] ?? ''),
                isset($validated['department_id']) ? (int) $validated['department_id'] : null,
                isset($validated['programme_id']) ? (int) $validated['programme_id'] : null
            );

            $selectedColumns = array_keys(self::COURSE_REGISTRATION_COLUMNS);
            $columnLabels = self::COURSE_REGISTRATION_COLUMNS;
        } else {
            $rows = match ($validated['type']) {
                'fresh' => $reportService->exportFreshStudents(
                    $validated['session'],
                    (string) ($validated['search'] ?? ''),
                    isset($validated['department_id']) ? (int) $validated['department_id'] : null,
                    isset($validated['programme_id']) ? (int) $validated['programme_id'] : null
                ),
                'returning' => $reportService->exportReturningStudents(
                    $validated['session'],
                    (string) ($validated['search'] ?? ''),
                    isset($validated['department_id']) ? (int) $validated['department_id'] : null,
                    isset($validated['programme_id']) ? (int) $validated['programme_id'] : null
                ),
                default => $reportService->exportAllStudents(
                    $validated['session'],
                    (string) ($validated['search'] ?? ''),
                    isset($validated['department_id']) ? (int) $validated['department_id'] : null,
                    isset($validated['programme_id']) ? (int) $validated['programme_id'] : null
                ),
            };

            $columnLabels = self::COLUMN_OPTIONS;
        }

        $format = (string) ($validated['format'] ?? 'csv');

        if ($format === 'pdf') {
            if ($dataset === 'course_registration') {
                return redirect()->route('admin.fubk.pdf.queue', [
                    'type' => (string) $validated['type'],
                    'dataset' => 'course_registration',
                    'session' => (string) $validated['session'],
                    'search' => (string) ($validated['search'] ?? ''),
                    'department_id' => isset($validated['department_id']) ? (int) $validated['department_id'] : null,
                    'programme_id' => isset($validated['programme_id']) ? (int) $validated['programme_id'] : null,
                    'columns' => (string) ($validated['columns'] ?? ''),
                ]);
            }

            $pdfView = $dataset === 'course_registration'
                ? 'admin.report.fubk-course-registration-pdf'
                : 'admin.report.fubk-students-pdf';

            $pdf = Pdf::loadView($pdfView, [
                'rows' => $rows,
                'columns' => $selectedColumns,
                'columnLabels' => $columnLabels,
                'reportType' => (string) $validated['type'],
                'session' => (string) $validated['session'],
                'dataset' => $dataset,
            ]);

            $pdf->setPaper('A4', 'landscape');

            return new StreamedResponse(function () use ($pdf): void {
                echo $pdf->output();
            }, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="fubk-' . $validated['type'] . '-' . $dataset . '-' . now()->format('Y-m-d-His') . '.pdf"',
            ]);
        }

        $filename = sprintf('fubk-%s-%s-%s.csv', $validated['type'], $dataset, now()->format('Y-m-d-His'));

        return response()->streamDownload(function () use ($rows, $selectedColumns, $dataset): void {
            $handle = fopen('php://output', 'w');

            $labels = $dataset === 'course_registration' ? self::COURSE_REGISTRATION_COLUMNS : self::COLUMN_OPTIONS;

            fputcsv($handle, array_map(fn(string $column): string => $labels[$column], $selectedColumns));

            foreach ($rows as $row) {
                $csvRow = [];

                foreach ($selectedColumns as $column) {
                    $csvRow[] = $column === 'registered_courses'
                        ? $row['registered_courses_text']
                        : ($row[$column] ?? '');
                }

                fputcsv($handle, $csvRow);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
