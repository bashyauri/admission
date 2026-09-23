<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Exports\NyscMobilizationExport;
use App\Services\ResultReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class SenatePassListController
{
    public function __construct(
        protected ResultReportingService $reportingService
    ) {}

    /**
     * Display the Senate Pass List print view.
     */
    public function print(
        Request $request,
        string $session,
        string|int|null $department = null
    ): View {
        $user = Auth::user();

        if (!$user || !$user->canActAsExamOfficer()) {
            abort(403, 'Unauthorized access to Senate Pass List.');
        }

        $normalizedSession = str_replace('-', '/', $session);
        $departmentId = $this->normalizeDepartmentId($department);

        $data = $this->reportingService->getSenatePassList([
            'academic_session' => $normalizedSession,
            'department_id' => $departmentId,
            'cleared_only' => true,
        ]);

        return view('reports.senate-pass-list', $data);
    }

    /**
     * Export Senate Pass List as CSV.
     */
    public function exportCsv(
        Request $request,
        string $session,
        string|int|null $department = null
    ): BinaryFileResponse {
        $user = Auth::user();

        if (!$user || !$user->canActAsExamOfficer()) {
            abort(403, 'Unauthorized access to Senate Pass List export.');
        }

        $normalizedSession = str_replace('-', '/', $session);
        $departmentId = $this->normalizeDepartmentId($department);

        $data = $this->reportingService->getSenatePassList([
            'academic_session' => $normalizedSession,
            'department_id' => $departmentId,
            'cleared_only' => true,
        ]);

        $filename = 'Senate_Pass_List_'
            . str_replace('/', '-', $normalizedSession)
            . '_'
            . ($data['department']['name'] ?? 'All_Departments')
            . '.csv';

        return Excel::download(
            new NyscMobilizationExport($data),
            $filename,
            \Maatwebsite\Excel\Excel::CSV,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }

    /**
     * Normalize the department route parameter to an integer ID.
     *
     * Laravel route parameters arrive as strings, even when
     * the value represents a numeric department ID.
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