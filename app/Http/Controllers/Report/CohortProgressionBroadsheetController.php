<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\StudentLevel;
use App\Services\ResultReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CohortProgressionBroadsheetController extends Controller
{
    public function __construct(
        protected ResultReportingService $reportingService
    ) {}

    /**
     * Display and print official Cohort Progression Master Broadsheet (All Sessions & Carry-Over Audit).
     *
     * @param Request $request
     * @param Department|int $department
     * @param string $admissionSession
     * @return View
     */
    public function print(
        Request $request,
        Department|int $department,
        string $admissionSession
    ): View {
        $departmentId = $department instanceof Department 
            ? $department->id 
            : (int) $department;

        $normalizedSession = str_replace('-', '/', $admissionSession);
        $levelId = $request->query('level') ? (int) $request->query('level') : null;

        $filters = [
            'department_id' => $departmentId,
            'admission_session' => $normalizedSession,
            'student_level_id' => $levelId,
            'course_id' => $request->query('course_id') ? (int) $request->query('course_id') : null,
            'status' => $request->query('status'),
        ];

        $data = $this->reportingService->getCohortProgressionBroadsheet($filters);

        // Fetch options for on-page toolbar filters
        $allDepartments = Department::orderBy('name')->get(['id', 'name']);
        $allLevels = StudentLevel::orderBy('level')->get(['id', 'level']);
        $allCohorts = DB::table('academic_details')
            ->whereNotNull('admission_session')
            ->where('admission_session', '!=', '')
            ->distinct()
            ->orderByDesc('admission_session')
            ->pluck('admission_session')
            ->toArray();

        // Also add sessions from results if not already present
        $resultSessions = DB::table('results')
            ->whereNotNull('academic_session')
            ->where('academic_session', '!=', '')
            ->distinct()
            ->pluck('academic_session')
            ->toArray();

        $mergedCohorts = array_values(array_unique(array_filter(array_merge($allCohorts, $resultSessions))));
        rsort($mergedCohorts);

        $viewData = array_merge($data, [
            'allDepartments' => $allDepartments,
            'allLevels' => $allLevels,
            'allCohorts' => $mergedCohorts,
            'selectedLevelId' => $levelId,
        ]);

        return view('reports.cohort-progression-broadsheet', $viewData);
    }

    /**
     * Export official Cohort Progression Master Broadsheet as a standard CSV download.
     *
     * @param Request $request
     * @param Department|int $department
     * @param string $admissionSession
     * @return StreamedResponse
     */
    public function exportCsv(
        Request $request,
        Department|int $department,
        string $admissionSession
    ): StreamedResponse {
        $departmentId = $department instanceof Department 
            ? $department->id 
            : (int) $department;

        $normalizedSession = str_replace('-', '/', $admissionSession);

        $filters = [
            'department_id' => $departmentId,
            'admission_session' => $normalizedSession,
            'course_id' => $request->query('course_id') ? (int) $request->query('course_id') : null,
            'status' => $request->query('status'),
        ];

        $data = $this->reportingService->getCohortProgressionBroadsheet($filters);
        $students = $data['students'] ?? [];

        $cleanDept = preg_replace('/[^A-Za-z0-9]/', '_', $data['department']['name'] ?? 'All_Depts');
        $cleanSession = str_replace('/', '_', $normalizedSession);
        $filename = "Cohort_Progression_Broadsheet_{$cleanDept}_{$cleanSession}.csv";

        return response()->streamDownload(function () use ($students, $data, $normalizedSession) {
            $handle = fopen('php://output', 'w');

            // Institutional header rows
            fputcsv($handle, ['WAZIRI UMARU FEDERAL POLYTECHNIC BIRNIN KEBBI']);
            fputcsv($handle, ['IN AFFILIATION WITH FEDERAL UNIVERSITY BIRNIN KEBBI']);
            fputcsv($handle, ['COHORT PROGRESSION MASTER BROADSHEET & CARRY-OVER AUDIT']);
            fputcsv($handle, ['ADMISSION COHORT SESSION:', $normalizedSession]);
            fputcsv($handle, ['DEPARTMENT:', $data['department']['name'] ?? 'All Departments']);
            fputcsv($handle, ['PROGRAMME:', $data['programme'] ?? 'All Programmes']);
            fputcsv($handle, ['DATE GENERATED:', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // Summary Statistics
            $stats = $data['statistics'] ?? [];
            fputcsv($handle, ['COHORT AUDIT SUMMARY']);
            fputcsv($handle, ['Total Students', $stats['total_students'] ?? 0]);
            fputcsv($handle, ['Clean Progression (0 Carry-Overs)', $stats['clean_record_count'] ?? 0, ($stats['clean_record_percentage'] ?? 0) . '%']);
            fputcsv($handle, ['Resolved Carry-Overs (All Cleared)', $stats['resolved_carryover_count'] ?? 0, ($stats['resolved_carryover_percentage'] ?? 0) . '%']);
            fputcsv($handle, ['Active Carry-Over Deficiencies', $stats['deficient_count'] ?? 0, ($stats['deficient_percentage'] ?? 0) . '%']);
            fputcsv($handle, ['Total Deficiencies Recorded / Cleared / Outstanding', 
                $stats['total_carryovers_recorded'] ?? 0, 
                $stats['total_carryovers_cleared'] ?? 0, 
                $stats['total_carryovers_outstanding'] ?? 0
            ]);
            fputcsv($handle, []);

            // Table headers
            fputcsv($handle, [
                'S/N',
                'Matriculation Number',
                'Full Name',
                'Entry Mode',
                'Current Level',
                'Session Performance Progression (Session: TCR | TCP | GPA | CGPA)',
                'Cumulative TCR',
                'Cumulative TCP',
                'Final CGPA',
                'Class of Degree',
                'Carry-Overs Incurred vs Cleared Ledger',
                'Senate Audit Standing',
            ]);

            // Data rows
            foreach ($students as $index => $row) {
                // Format progression string
                $progressionParts = [];
                foreach ($row['sessions'] as $sess) {
                    $progressionParts[] = "{$sess['session']}: TCR={$sess['tcr']}, TCP={$sess['tcp']}, GPA={$sess['session_gpa']}, CGPA={$sess['running_cgpa']}";
                }
                $progressionString = implode(' | ', $progressionParts);

                // Format carry-over audit string
                $carryOverParts = [];
                if (empty($row['carry_overs'])) {
                    $carryOverString = 'None (Clean)';
                } else {
                    foreach ($row['carry_overs'] as $co) {
                        if ($co['is_cleared']) {
                            $carryOverParts[] = "[{$co['course_code']} (Failed: {$co['failed_session']} {$co['failed_semester']} with {$co['failed_score']}/{$co['failed_grade']} -> CLEARED in {$co['retake_session']} with {$co['cleared_score']}/{$co['cleared_grade']})]";
                        } else {
                            $carryOverParts[] = "[{$co['course_code']} (Failed: {$co['failed_session']} {$co['failed_semester']} with {$co['failed_score']}/{$co['failed_grade']} -> OUTSTANDING)]";
                        }
                    }
                    $carryOverString = implode('; ', $carryOverParts);
                }

                fputcsv($handle, [
                    $index + 1,
                    $row['matric_no'],
                    $row['student_name'],
                    $row['entry_mode'],
                    $row['current_level'],
                    $progressionString,
                    $row['total_ccr'],
                    $row['total_ccp'],
                    number_format((float) $row['final_cgpa'], 2),
                    $row['class_of_degree'],
                    $carryOverString,
                    $row['standing_remark'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
