<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Enums\StudentStatus;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\StudentStatusAudit;
use App\Models\StudentStatusRecord;
use App\Models\User;
use App\Services\StudentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReinstatementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private StudentStatusService $service;
    private User $admin;
    private User $student;
    private StudentStatusRecord $withdrawal;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StudentStatusService::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);

        $department = Department::create(['name' => 'Computer Science']);
        $programme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate(['id' => ProgrammesEnum::Undergraduate->value, 'name' => 'BSc Computer Science', 'abv' => 'UG']);
        $course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semesters' => 8,
        ]);
        $level = StudentLevel::create(['level' => '300']);

        $this->student = User::factory()->create([
            'role' => 'student',
            'programme_id' => $programme->id,
        ]);
        AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'UG/2022/001',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'student_level_id' => $level->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2022/2023',
            'course_id' => $course->id,
        ]);

        $this->withdrawal = $this->service->processVoluntaryWithdrawal(
            user: $this->student,
            reason: 'Personal circumstances',
            academicSession: '2024/2025',
            effectiveDate: '2025-01-10',
            senateReference: 'SEN-2025-010'
        );
    }

    public function test_senate_approval_appends_reinstatement_and_updates_return_placement(): void
    {
        $request = $this->service->requestReinstatement($this->student, notes: 'Ready to resume');
        $request = $this->service->reviewReinstatement($request, 'DEPARTMENT', true);
        $request = $this->service->reviewReinstatement($request, 'FACULTY', true);
        $placement = $this->service->determineReinstatementLevel($this->student, $this->withdrawal);

        $event = $this->service->processReinstatement(
            request: $request,
            approved: true,
            senateReference: 'SEN-2026-014',
            senateDecisionDate: '2026-08-20'
        );

        $this->assertSame(StudentStatus::REINSTATED, $event->status);
        $this->assertSame(StudentStatusService::WORKFLOW_SENATE_APPROVED, $event->senate_decision);
        $this->assertSame('SEN-2026-014', $event->senate_reference);
        $this->assertDatabaseHas('student_status_records', [
            'id' => $this->withdrawal->id,
            'status' => StudentStatus::VOLUNTARY_WITHDRAWAL->value,
            'end_date' => null,
        ]);
        $this->assertSame(3, StudentStatusRecord::where('user_id', $this->student->id)->count());
        $this->assertSame(5, StudentStatusAudit::where('student_id', $this->student->id)->count());
        $this->assertDatabaseHas('student_status_audits', [
            'student_id' => $this->student->id,
            'action' => 'REINSTATEMENT_APPROVED',
            'senate_reference' => 'SEN-2026-014',
        ]);
        $this->assertSame($placement['student_level_id'], $this->student->academicDetail->fresh()->student_level_id);
        $this->assertSame('2025/2026', $this->student->academicDetail->fresh()->acad_session);
        $this->assertTrue($this->service->isAcademicallyActive($this->student));
    }

    public function test_senate_rejection_keeps_withdrawal_as_authoritative_status(): void
    {
        $request = $this->service->requestReinstatement($this->student);
        $request = $this->service->reviewReinstatement($request, 'DEPARTMENT', true);
        $request = $this->service->reviewReinstatement($request, 'FACULTY', true);

        $event = $this->service->processReinstatement($request, false, 'SEN/2026/015');

        $this->assertSame(StudentStatus::VOLUNTARY_WITHDRAWAL, $event->status);
        $this->assertSame(StudentStatusService::REINSTATEMENT_REJECTED, $event->senate_decision);
        $this->assertSame($this->withdrawal->id, $this->service->getCurrentStatus($this->student)->id);
        $this->assertSame(3, StudentStatusRecord::where('user_id', $this->student->id)->count());
        $this->assertDatabaseHas('student_status_audits', [
            'student_id' => $this->student->id,
            'action' => 'REINSTATEMENT_REJECTED',
            'senate_reference' => 'SEN/2026/015',
        ]);
    }

    public function test_review_stages_must_be_completed_in_order(): void
    {
        $request = $this->service->requestReinstatement($this->student);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not ready for FACULTY review');

        $this->service->reviewReinstatement($request, 'FACULTY', true);
    }

    public function test_postgraduate_student_cannot_request_reinstatement(): void
    {
        $pg = User::factory()->create(['role' => 'student', 'programme_id' => ProgrammesEnum::PG->value]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('only applicable to undergraduate');

        $this->service->requestReinstatement($pg);
    }
}
