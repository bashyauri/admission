<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Services\AcademicSessionService;
use App\Services\Report\FubkStudentReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class FubkReport extends Component
{
    use WithPagination;

    private const BIODATA_DEFAULT_COLUMNS = [
        'full_name',
        'gender',
        'email',
        'phone',
        'matric_no',
        'jamb_no',
        'nationality',
        'state',
        'lga',
        'department_name',
        'programme_name',
        'admission_session',
    ];

    private const FRESH_DEFAULT_COLUMNS = [
        ...self::BIODATA_DEFAULT_COLUMNS,
        'registered_courses',
    ];

    private const RETURNING_DEFAULT_COLUMNS = self::BIODATA_DEFAULT_COLUMNS;

    private const ALL_DEFAULT_COLUMNS = self::BIODATA_DEFAULT_COLUMNS;

    public const COLUMN_OPTIONS = [
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

    #[Url(as: 'tab')]
    public string $reportType = 'fresh';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'per')]
    public int $perPage = 15;

    #[Url(as: 'dept')]
    public string $departmentId = '';

    #[Url(as: 'prog')]
    public string $programmeId = '';

    public array $selectedColumns = self::FRESH_DEFAULT_COLUMNS;

    public string $academicSession = '';

    public function mount(AcademicSessionService $academicSessionService): void
    {
        $this->academicSession = $academicSessionService->getAcademicSession(Auth::user());

        if (!in_array($this->reportType, ['fresh', 'returning', 'all'], true)) {
            $this->reportType = 'fresh';
        }

        $this->normalizeSelectedColumns();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingDepartmentId(): void
    {
        $this->resetPage();
    }

    public function updatingProgrammeId(): void
    {
        $this->resetPage();
    }

    public function showFresh(): void
    {
        $this->reportType = 'fresh';
        $this->selectedColumns = self::FRESH_DEFAULT_COLUMNS;
        $this->normalizeSelectedColumns();
        $this->resetPage();
    }

    public function showReturning(): void
    {
        $this->reportType = 'returning';
        $this->selectedColumns = self::RETURNING_DEFAULT_COLUMNS;
        $this->normalizeSelectedColumns();
        $this->resetPage();
    }

    public function showAll(): void
    {
        $this->reportType = 'all';
        $this->selectedColumns = self::ALL_DEFAULT_COLUMNS;
        $this->normalizeSelectedColumns();
        $this->resetPage();
    }

    public function updatedSelectedColumns(): void
    {
        $this->normalizeSelectedColumns();
    }

    private function normalizeSelectedColumns(): void
    {
        $allowedColumns = array_keys(self::COLUMN_OPTIONS);

        $this->selectedColumns = array_values(array_intersect($allowedColumns, $this->selectedColumns));

        if ($this->selectedColumns === []) {
            $this->selectedColumns = ['full_name'];
        }
    }

    public function getExportUrlProperty(): string
    {
        return $this->buildExportUrl('csv', 'biodata');
    }

    public function getPdfUrlProperty(): string
    {
        return $this->buildExportUrl('pdf', 'biodata');
    }

    public function getCourseCsvUrlProperty(): string
    {
        return $this->buildExportUrl('csv', 'course_registration');
    }

    public function getCoursePdfUrlProperty(): string
    {
        return route('admin.fubk.pdf.queue', [
            'type' => $this->reportType,
            'dataset' => 'course_registration',
            'session' => $this->academicSession,
            'search' => $this->search,
            'department_id' => $this->departmentId !== '' ? (int) $this->departmentId : null,
            'programme_id' => $this->programmeId !== '' ? (int) $this->programmeId : null,
            'columns' => implode(',', $this->selectedColumns),
        ]);
    }

    private function buildExportUrl(string $format, string $dataset): string
    {
        return route('admin.fubk.export', [
            'type' => $this->reportType,
            'format' => $format,
            'dataset' => $dataset,
            'session' => $this->academicSession,
            'search' => $this->search,
            'department_id' => $this->departmentId !== '' ? (int) $this->departmentId : null,
            'programme_id' => $this->programmeId !== '' ? (int) $this->programmeId : null,
            'columns' => implode(',', $this->selectedColumns),
        ]);
    }

    public function render(FubkStudentReportService $reportService): View
    {
        $selectedDepartmentId = $this->departmentId !== '' ? (int) $this->departmentId : null;
        $selectedProgrammeId = $this->programmeId !== '' ? (int) $this->programmeId : null;

        $students = match ($this->reportType) {
            'fresh' => $reportService->getFreshStudents($this->academicSession, $this->search, $this->perPage, $selectedDepartmentId, $selectedProgrammeId),
            'returning' => $reportService->getReturningStudents($this->academicSession, $this->search, $this->perPage, $selectedDepartmentId, $selectedProgrammeId),
            default => $reportService->getAllStudents($this->academicSession, $this->search, $this->perPage, $selectedDepartmentId, $selectedProgrammeId),
        };

        return view('livewire.admin.fubk-report', [
            'students' => $students,
            'departments' => $reportService->getDepartments(),
            'programmes' => $reportService->getProgrammes(),
            'columnOptions' => self::COLUMN_OPTIONS,
        ]);
    }
}
