<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentStatusRecord;
use App\Models\User;
use App\Services\ResultReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalReportingTest extends TestCase
{
    use RefreshDatabase;

    private ResultReportingService $reporting;
    private Department $department;
    private Department $otherDepartment;
    private Programme $ugProgramme;
    private Programme $pgProgramme;
    private User $academicWithdrawalStudent;
    private User $medicalWithdrawalStudent;
    private User $postgraduateStudent;
    private StudentStatusRecord $academicWithdrawal;
    private StudentStatusRecord $medicalWithdrawal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reporting = app(ResultReportingService::class);
        $this->ugProgramme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate(['id' => ProgrammesEnum::Undergraduate->value, 'name' => 'BSc Computer Science', 'abv' => 'UG']);
        $this->pgProgramme = Programme::find(ProgrammesEnum::PG->value)
            ?? Programme::forceCreate(['id' => ProgrammesEnum::PG->value, 'name' => 'MSc Computer Science', 'abv' => 'PG']);
        $this->department = Department::create(['name' => 'Computer Science']);
        $this->otherDepartment = Department::create(['name' => 'Mathematics']);
        $course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $this->ugProgramme->id,
            'semesters' => 8,
        ]);
        $otherCourse = Course::create([
            'name' => 'Mathematics',
            'department_id' => $this->otherDepartment->id,
            'programme_id' => $this->ugProgramme->id,
            'semesters' => 8,
        ]);

        $this->academicWithdrawalStudent = $this->makeStudent('academic', 'UG/2021/001', $course, $this->ugProgramme, $this->department);
        $this->medicalWithdrawalStudent = $this->makeStudent('medical', 'UG/2020/002', $otherCourse, $this->ugProgramme, $this->otherDepartment);
        $this->postgraduateStudent = $this->makeStudent('postgraduate', 'PG/2021/001', null, $this->pgProgramme, null);

        $this->academicWithdrawal = $this->makeWithdrawal(
            $this->academicWithdrawalStudent,
            StudentStatus::ACADEMIC_WITHDRAWAL,
            'academic',
            '2023/2024',
            '2024-02-01',
            'SEN-2024-101',
            'SENATE_APPROVED'
        );
        $this->medicalWithdrawal = $this->makeWithdrawal(
            $this->medicalWithdrawalStudent,
            StudentStatus::MEDICAL_WITHDRAWAL,
            'medical',
            '2024/2025',
            '2025-03-01',
            'SEN/2025/020',
            'PENDING_SENATE'
        );
        $this->makeWithdrawal(
            $this->postgraduateStudent,
            StudentStatus::ACADEMIC_WITHDRAWAL,
            'academic',
            '2023/2024',
            '2024-02-01',
            'SEN-2024-102',
            'SENATE_APPROVED'
        );

        $request = StudentStatusRecord::create([
            'user_id' => $this->academicWithdrawalStudent->id,
            'academic_detail_id' => $this->academicWithdrawal->academic_detail_id,
            'status' => StudentStatus::REINSTATED,
            'status_type' => StudentStatusType::ACADEMIC,
            'reason_code' => 'REINSTATEMENT_REQUEST',
            'academic_session' => '2023/2024',
            'effective_date' => '2025-01-10',
            'senate_decision' => 'REINSTATEMENT_APPROVED',
            'senate_reference' => 'SEN-2025-090',
        ]);
        StudentStatusRecord::create([
            'user_id' => $this->academicWithdrawalStudent->id,
            'academic_detail_id' => $request->academic_detail_id,
            'status' => StudentStatus::REINSTATED,
            'status_type' => StudentStatusType::ACADEMIC,
            'reason_code' => 'SENATE_REINSTATEMENT',
            'academic_session' => '2025/2026',
            'effective_date' => '2025-08-01',
            'senate_decision' => 'SENATE_APPROVED',
            'senate_reference' => 'SEN-2025-090',
        ]);
    }

    private function makeStudent(string $prefix, string $matricNo, ?Course $course, Programme $programme, ?Department $department): User
    {
        $student = User::factory()->create([
            'role' => 'student',
            'programme_id' => $programme->id,
            'surname' => ucfirst($prefix),
            'firstname' => 'Student',
        ]);

        if ($course && $department) {
            AcademicDetail::create([
                'user_id' => $student->id,
                'matric_no' => $matricNo,
                'department_id' => $department->id,
                'programme_id' => $programme->id,
                'course_id' => $course->id,
                'student_level_id' => 1,
                'acad_session' => '2025/2026',
                'admission_session' => '2021/2022',
            ]);
        }

        return $student;
    }

    private function makeWithdrawal(
        User $student,
        StudentStatus $status,
        string $type,
        string $session,
        string $effectiveDate,
        ?string $reference,
        string $decision
    ): StudentStatusRecord {
        return StudentStatusRecord::create([
            'user_id' => $student->id,
            'academic_detail_id' => $student->academicDetail?->id,
            'status' => $status,
            'status_type' => $type,
            'reason_code' => strtoupper($type),
            'reason' => 'Test withdrawal report record',
            'academic_session' => $session,
            'effective_date' => $effectiveDate,
            'senate_reference' => $reference,
            'senate_decision_date' => $decision === 'PENDING_SENATE' ? null : $effectiveDate,
            'senate_decision' => $decision,
            'reinstatement_eligible' => true,
        ]);
    }

    public function test_ledger_filters_and_excludes_postgraduate_status_records(): void
    {
        $all = $this->reporting->getWithdrawalLedger();
        $this->assertCount(2, $all['rows']);
        $this->assertSame(2, $all['summary']['total_withdrawals']);

        $filtered = $this->reporting->getWithdrawalLedger([
            'academic_session' => '2023/2024',
            'department_id' => $this->department->id,
            'withdrawal_type' => StudentStatus::ACADEMIC_WITHDRAWAL->value,
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31',
            'reinstatement_eligible' => '1',
            'senate_reference' => '2024-101',
        ]);

        $this->assertCount(1, $filtered['rows']);
        $this->assertSame('UG/2021/001', $filtered['rows']->first()['matric_no']);
        $this->assertSame('Reinstated', $filtered['rows']->first()['reinstatement_status']);
        $this->assertSame('2025/2026', $filtered['rows']->first()['reinstatement_session']);
    }

    public function test_senate_and_departmental_reports_aggregate_withdrawals(): void
    {
        $senate = $this->reporting->getSenateWithdrawalReport();
        $this->assertSame(1, $senate['summary']['senate_approved']);
        $this->assertSame(1, $senate['summary']['pending_senate']);
        $this->assertSame(0, $senate['missing_senate_reference_count']);

        $departmental = $this->reporting->getDepartmentalWithdrawalReport();
        $this->assertCount(2, $departmental['department_summary']);
        $this->assertSame(1, $departmental['department_summary']->firstWhere('department', 'Computer Science')['reinstated']);
    }

    public function test_reinstatement_report_tracks_request_and_approved_event(): void
    {
        $report = $this->reporting->getReinstatementReport();

        $this->assertSame(1, $report['summary']['requested']);
        $this->assertSame(1, $report['summary']['reinstated']);
        $this->assertSame('SEN-2025-090', $report['rows']->first()['reinstatement_reference']);
    }

    public function test_exam_officer_can_view_export_csv_and_pdf(): void
    {
        $examOfficer = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($examOfficer)
            ->get(route('exam-officer.withdrawal-ledger'))
            ->assertOk()
            ->assertSee('Student Withdrawal Ledger')
            ->assertSee('UG/2021/001')
            ->assertDontSee('PG/2021/001');

        $this->actingAs($examOfficer)
            ->get(route('exam-officer.withdrawal-ledger.export.csv', ['report' => 'senate']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($examOfficer)
            ->get(route('exam-officer.withdrawal-ledger.export.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
