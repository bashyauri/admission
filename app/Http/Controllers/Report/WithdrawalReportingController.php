<?php

declare(strict_types=1);

namespace App\Http\Controllers\Report;

use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Http\Controllers\Controller;
use App\Services\ResultReportingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class WithdrawalReportingController extends Controller
{
    public function __construct(protected ResultReportingService $reportingService) {}

    public function index(Request $request): View
    {
        $report = $this->reportType($request);
        $data = $this->getReportData($report, $this->filters($request));
        $allRows = collect($data['rows']);
        $perPage = 50;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $data['rows'] = new LengthAwarePaginator(
            $allRows->forPage($page, $perPage)->values(),
            $allRows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $data['report'] = $report;

        return view('reports.withdrawal-ledger', $data);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $report = $this->reportType($request);
        $data = $this->getReportData($report, $this->filters($request));
        $filename = 'Withdrawal_Report_' . ucfirst($report) . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data, $report): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                throw new \RuntimeException('Unable to open the report output stream.');
            }

            fputcsv($handle, [$data['report_title']]);
            fputcsv($handle, ['Generated', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            if ($report === 'departmental') {
                fputcsv($handle, ['Department', 'Total Withdrawals', 'Academic', 'Voluntary', 'Medical', 'Pending Senate', 'Reinstated']);
                foreach ($data['department_summary'] as $row) {
                    fputcsv($handle, array_values($row));
                }
            } else {
                fputcsv($handle, [
                    'Matric No', 'Student', 'Programme', 'Department', 'Withdrawal Type', 'Session',
                    'Semester', 'Effective Date', 'Senate Decision', 'Senate Reference',
                    'Reinstatement Eligible', 'Reinstatement Status', 'Reinstatement Session',
                    'Reinstatement Reference', 'Reinstatement Date',
                ]);
                foreach ($data['rows'] as $row) {
                    fputcsv($handle, [
                        $row['matric_no'], $row['student_name'], $row['programme'], $row['department'],
                        $row['withdrawal_type'], $row['academic_session'], $row['semester'], $row['effective_date'],
                        $row['senate_decision'], $row['senate_reference'], $row['reinstatement_eligible'] ? 'Yes' : 'No',
                        $row['reinstatement_status'], $row['reinstatement_session'], $row['reinstatement_reference'], $row['reinstatement_date'],
                    ]);
                }
            }

            fputcsv($handle, []);
            foreach ([
                'Withdrawals by academic session' => $data['by_session'],
                'Withdrawals by type' => $data['by_type'],
                'Withdrawals by department' => $data['by_department'],
            ] as $heading => $counts) {
                fputcsv($handle, [$heading]);
                fputcsv($handle, ['Category', 'Count']);
                foreach ($counts as $category => $count) {
                    fputcsv($handle, [$category, $count]);
                }
                fputcsv($handle, []);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        $report = $this->reportType($request);
        $data = $this->getReportData($report, $this->filters($request));
        $data['report'] = $report;

        return Pdf::loadView('reports.withdrawal-ledger-pdf', $data)
            ->setPaper('A4', 'landscape')
            ->download('Withdrawal_Report_' . ucfirst($report) . '_' . now()->format('Ymd_His') . '.pdf');
    }

    private function reportType(Request $request): string
    {
        $report = (string) $request->query('report', 'ledger');
        abort_unless(in_array($report, ['ledger', 'senate', 'departmental', 'reinstatement'], true), 404);

        return $report;
    }

    private function filters(Request $request): array
    {
        $withdrawalTypes = array_merge(
            array_map(fn (StudentStatus $status) => $status->value, array_filter(StudentStatus::cases(), fn (StudentStatus $status) => $status->isWithdrawn())),
            StudentStatusType::values()
        );
        $withdrawalStatuses = array_map(
            fn (StudentStatus $status) => $status->value,
            array_filter(StudentStatus::cases(), fn (StudentStatus $status) => $status->isWithdrawn())
        );
        $decisions = ['WITHDRAWAL_RECOMMENDED', 'PENDING_SENATE', 'SENATE_APPROVED', 'SENATE_REJECTED'];

        return $request->validate([
            'academic_session' => ['nullable', 'regex:/^\d{4}\/\d{4}$/'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'programme_id' => ['nullable', 'integer', 'exists:programmes,id'],
            'withdrawal_type' => ['nullable', 'in:' . implode(',', $withdrawalTypes)],
            'status' => ['nullable', 'in:' . implode(',', array_merge($decisions, $withdrawalStatuses))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'reinstatement_eligible' => ['nullable', 'in:0,1'],
            'senate_reference' => ['nullable', 'string', 'max:100'],
            'report' => ['nullable', 'in:ledger,senate,departmental,reinstatement'],
        ]);
    }

    private function getReportData(string $report, array $filters): array
    {
        return match ($report) {
            'senate' => $this->reportingService->getSenateWithdrawalReport($filters),
            'departmental' => $this->reportingService->getDepartmentalWithdrawalReport($filters),
            'reinstatement' => $this->reportingService->getReinstatementReport($filters),
            default => $this->reportingService->getWithdrawalLedger($filters),
        };
    }
}
