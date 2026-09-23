<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\ResultReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SenateGraduationBroadsheetController extends Controller
{
    public function __construct(
        protected ResultReportingService $reportingService
    ) {}

    /**
     * Display and print official Senate Graduation Broadsheet
     * (Final Degree Master Sheet).
     */
    public function print(
        Request $request,
        string $session,
        string|int|null $department = null
    ): View {
        $departmentId = $this->normalizeDepartmentId($department);

        $normalizedSession = str_replace('-', '/', $session);

        $filters = [
            'academic_session' => $normalizedSession,
            'department_id' => $departmentId,
            'course_id' => $request->query('course_id')
                ? (int) $request->query('course_id')
                : null,
            'cleared_only' => $request->boolean('cleared_only', false),
        ];

        $data = $this->reportingService
            ->getSenateGraduationBroadsheet($filters);

        return view(
            'reports.senate-graduation-broadsheet',
            $data
        );
    }

    /**
     * Export official Senate Graduation Broadsheet as a standard CSV download.
     */
    public function exportCsv(
        Request $request,
        string $session,
        string|int|null $department = null
    ): StreamedResponse {
        $departmentId = $this->normalizeDepartmentId($department);

        $normalizedSession = str_replace('-', '/', $session);

        $filters = [
            'academic_session' => $normalizedSession,
            'department_id' => $departmentId,
            'course_id' => $request->query('course_id')
                ? (int) $request->query('course_id')
                : null,
            'cleared_only' => $request->boolean('cleared_only', false),
        ];

        $data = $this->reportingService
            ->getSenateGraduationBroadsheet($filters);

        $graduands = $data['graduands'] ?? [];

        $cleanDept = preg_replace(
            '/[^A-Za-z0-9]/',
            '_',
            $data['department']['name'] ?? 'All_Depts'
        );

        $cleanSession = str_replace('/', '_', $normalizedSession);

        $filename = "Senate_Graduation_Broadsheet_{$cleanDept}_{$cleanSession}.csv";

        return response()->streamDownload(
            function () use (
                $graduands,
                $data,
                $normalizedSession
            ): void {
                $handle = fopen('php://output', 'w');

                if ($handle === false) {
                    throw new \RuntimeException(
                        'Unable to open output stream.'
                    );
                }

                // Header information rows
                fputcsv($handle, [
                    'WAZIRI UMARU FEDERAL POLYTECHNIC BIRNIN KEBBI'
                ]);

                fputcsv($handle, [
                    'IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI'
                ]);

                fputcsv($handle, [
                    'OFFICIAL SENATE GRADUATION BROADSHEET & DEGREE CONFERMENT LIST'
                ]);

                fputcsv($handle, [
                    'ACADEMIC SESSION:',
                    $normalizedSession
                ]);

                fputcsv($handle, [
                    'DEPARTMENT:',
                    $data['department']['name'] ?? 'All Departments'
                ]);

                fputcsv($handle, [
                    'PROGRAMME:',
                    $data['programme'] ?? 'All Programmes'
                ]);

                fputcsv($handle, [
                    'DATE GENERATED:',
                    now()->format('Y-m-d H:i:s')
                ]);

                fputcsv($handle, []);

                // Table headers
                fputcsv($handle, [
                    'S/N',
                    'Matriculation Number',
                    'Candidate Full Name',
                    'Entry Session',
                    'Mode of Entry',
                    'Department',
                    'Programme',
                    'Required Units',
                    'Earned Units',
                    'CQP',
                    'Final CGPA',
                    'Class of Degree',
                    'Clearance Status',
                    'Senate Remarks',
                ]);

                // Graduand rows
                foreach ($graduands as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row['matric_no'],
                        $row['student_name'],
                        $row['entry_session'],
                        $row['mode_of_entry'],
                        $row['department_name'],
                        $row['programme_name'],
                        $row['total_units_required'],
                        $row['total_units_earned'],
                        $row['cqp'],
                        number_format(
                            (float) $row['final_cgpa'],
                            2
                        ),
                        $row['class_of_degree'],
                        $row['status_text'],
                        $row['remarks'],
                    ]);
                }

                fputcsv($handle, []);

                fputcsv($handle, [
                    '--- SENATE DEGREE CLASSIFICATION SUMMARY ---'
                ]);

                $summary = $data['summary'] ?? [];

                fputcsv($handle, [
                    'Total Candidates Audited',
                    $summary['total_graduands'] ?? 0
                ]);

                fputcsv($handle, [
                    'Officially Cleared Graduands',
                    $summary['cleared_count'] ?? 0
                ]);

                fputcsv($handle, [
                    'First Class Honours',
                    ($summary['first_class_count'] ?? 0)
                    . ' ('
                    . ($summary['first_class_percentage'] ?? 0)
                    . '%)'
                ]);

                fputcsv($handle, [
                    'Second Class Upper Division',
                    ($summary['second_upper_count'] ?? 0)
                    . ' ('
                    . ($summary['second_upper_percentage'] ?? 0)
                    . '%)'
                ]);

                fputcsv($handle, [
                    'Second Class Lower Division',
                    ($summary['second_lower_count'] ?? 0)
                    . ' ('
                    . ($summary['second_lower_percentage'] ?? 0)
                    . '%)'
                ]);

                fputcsv($handle, [
                    'Third Class Honours',
                    ($summary['third_class_count'] ?? 0)
                    . ' ('
                    . ($summary['third_class_percentage'] ?? 0)
                    . '%)'
                ]);

                fputcsv($handle, [
                    'Pass Degree',
                    ($summary['pass_count'] ?? 0)
                    . ' ('
                    . ($summary['pass_percentage'] ?? 0)
                    . '%)'
                ]);

                fputcsv($handle, [
                    'Deficient / Unapproved',
                    ($summary['deficient_count'] ?? 0)
                    . ' ('
                    . ($summary['deficient_percentage'] ?? 0)
                    . '%)'
                ]);

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]
        );
    }

    /**
     * Normalize the department route parameter to an integer ID.
     *
     * Laravel route parameters arrive as strings, even when the
     * underlying value represents a numeric department ID.
     */
    private function normalizeDepartmentId(
        string|int|null $department
    ): ?int {
        if ($department === null || $department === '' || strtolower(trim((string) $department)) === 'all') {
            return null;
        }

        return (int) $department;
    }
}