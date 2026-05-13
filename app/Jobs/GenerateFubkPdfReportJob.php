<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Report\FubkStudentReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateFubkPdfReportJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 1;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(private readonly string $token, private readonly array $payload)
    {
        $this->onQueue('reports');
    }

    public function handle(FubkStudentReportService $reportService): void
    {
        ini_set('memory_limit', '1024M');

        $type = (string) ($this->payload['type'] ?? 'all');
        $dataset = (string) ($this->payload['dataset'] ?? 'course_registration');
        $session = (string) ($this->payload['session'] ?? '');
        $search = (string) ($this->payload['search'] ?? '');
        $departmentId = is_numeric($this->payload['department_id'] ?? null) ? (int) $this->payload['department_id'] : null;
        $programmeId = is_numeric($this->payload['programme_id'] ?? null) ? (int) $this->payload['programme_id'] : null;

        if ($dataset === 'course_registration') {
            $rows = $reportService->exportCourseRegistrations($type, $session, $search, $departmentId, $programmeId);
            $columns = [
                'student_name',
                'matric_no',
                'department_name',
                'programme_name',
                'academic_session',
                'level_name',
                'semester',
                'course_code',
                'course_title',
                'credit_units',
            ];
            $labels = [
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
            $view = 'admin.report.fubk-course-registration-pdf';
        } else {
            $rows = match ($type) {
                'fresh' => $reportService->exportFreshStudents($session, $search, $departmentId, $programmeId),
                'returning' => $reportService->exportReturningStudents($session, $search, $departmentId, $programmeId),
                default => $reportService->exportAllStudents($session, $search, $departmentId, $programmeId),
            };

            $columns = array_values(array_filter(array_map('trim', explode(',', (string) ($this->payload['columns'] ?? '')))));
            if ($columns === []) {
                $columns = ['full_name'];
            }

            $labels = [
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
            $view = 'admin.report.fubk-students-pdf';
        }

        $pdf = Pdf::loadView($view, [
            'rows' => $rows,
            'columns' => $columns,
            'columnLabels' => $labels,
            'reportType' => $type,
            'session' => $session,
            'dataset' => $dataset,
        ])->setPaper('A4', 'landscape');

        $filename = sprintf('fubk-%s-%s-%s.pdf', $type, $dataset, now()->format('Y-m-d-His'));
        $path = 'reports/fubk/' . $this->token . '.pdf';

        Storage::disk('local')->put($path, $pdf->output());

        Cache::put($this->cacheKey(), [
            'status' => 'completed',
            'message' => 'PDF is ready for download.',
            'user_id' => $this->payload['user_id'] ?? null,
            'token' => $this->token,
            'download_path' => $path,
            'filename' => $filename,
        ], now()->addDay());
    }

    public function failed(Throwable $exception): void
    {
        Cache::put($this->cacheKey(), [
            'status' => 'failed',
            'message' => 'PDF generation failed: ' . $exception->getMessage(),
            'user_id' => $this->payload['user_id'] ?? null,
            'token' => $this->token,
            'download_path' => null,
            'filename' => null,
        ], now()->addDay());
    }

    private function cacheKey(): string
    {
        return 'fubk_pdf_job:' . $this->token;
    }
}
