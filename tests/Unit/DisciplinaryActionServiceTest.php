<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryActionAudit;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\StudentStatusRecord;
use App\Models\StudentStatusAudit;
use App\Models\User;
use App\Models\UserCapability;
use App\Services\DisciplinaryActionService;
use App\Services\AcademicProgressionService;
use App\Services\AcademicSessionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class DisciplinaryActionServiceTest extends TestCase
{
    use RefreshDatabase;

    private Programme $programme;
    private Department $department;
    private Course $programmeCourse;
    private StudentLevel $level;
    private User $student;
    private User $officer;
    private AcademicDetail $academicDetail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->programme = Programme::create(['id' => 7, 'name' => 'B.Sc Computer Science', 'abv' => 'UG']);
        $this->department = Department::create(['name' => 'Computer Science Department']);
        $this->programmeCourse = Course::create([
            'name' => 'Computer Science Programme',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);
        $this->level = StudentLevel::create(['level' => '300']);
        $this->student = $this->user('disciplinary_student@test.com', 'student', 7);
        $this->officer = $this->user('disciplinary_officer@test.com', 'admin', 7);
        $this->academicDetail = AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'UG/22/CS/1051',
            'course_id' => $this->programmeCourse->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2022/2023',
        ]);
        $this->actingAs($this->officer);
    }

    public function test_repeat_sanction_uses_progression_service_and_quash_restores_previous_state(): void
    {
        [$result] = $this->releasedResult();
        $action = app(DisciplinaryActionService::class)->applySanction($this->payload('repeat_session', [
            'effective_session' => '2024/2025',
        ]));

        $this->assertSame(2, AcademicProgressionRecord::where('user_id', $this->student->id)->count());
        $this->assertDatabaseHas('academic_progression_records', [
            'user_id' => $this->student->id,
            'academic_session' => '2024/2025',
            'standing' => 'REPEAT',
        ]);
        $this->assertStringContainsString('REPEAT SESSION (SDC: SEN-2026-042)', $result->fresh()->remarks);

        $lifted = app(DisciplinaryActionService::class)->recordAppealOutcome(
            $action->id,
            'quashed',
            'SEN-2026-043',
            'Senate allowed the appeal.'
        );

        $this->assertFalse($lifted->is_active);
        $this->assertSame('quashed', $lifted->appeal_status);
        $this->assertDatabaseCount('academic_progression_records', 0);
        $this->assertSame('Original released result.', $result->fresh()->remarks);
        $this->assertDatabaseHas('disciplinary_action_audits', [
            'disciplinary_action_id' => $action->id,
            'event' => 'SANCTION_QUASHED',
            'resolution_ref' => 'SEN-2026-043',
        ]);
    }

    public function test_course_cancellation_uses_result_course_snapshot_and_reverses_result_and_carry_over(): void
    {
        [$result] = $this->releasedResult();
        $result->update(['course_title_snapshot' => null]);
        ResultGpaRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024/2025',
            'semester_gpa' => 5.00,
            'cumulative_gpa' => 5.00,
            'total_credit_units' => 3,
            'total_grade_points' => 15,
            'cumulative_credit_units' => 3,
            'cumulative_grade_points' => 15,
        ]);

        $action = app(DisciplinaryActionService::class)->applySanction(
            $this->payload('course_cancellation', [
                'academic_session' => '2024/2025',
                'effective_session' => '2024/2025',
                'semester' => 'first',
                'result_id' => $result->id,
            ])
        );

        $this->assertSame('F', $result->fresh()->grade);
        $this->assertDatabaseHas('carry_over_courses', [
            'user_id' => $this->student->id,
            'department_course_id' => $result->department_course_id,
            'failed_session' => '2024/2025',
            'is_cleared' => false,
        ]);
        $audit = $action->auditEntries()->where('event', 'SANCTION_APPLIED')->firstOrFail();
        $this->assertSame('CSC301', $audit->metadata['course_snapshot']['course_code']);
        $this->assertSame('Data Structures', $audit->metadata['course_snapshot']['course_title']);

        app(DisciplinaryActionService::class)->liftSanction($action->id, 'SEN-2026-044');

        $this->assertSame('A', $result->fresh()->grade);
        $this->assertDatabaseCount('carry_over_courses', 0);
        $this->assertDatabaseHas('disciplinary_actions', ['id' => $action->id, 'is_active' => false]);
    }

    public function test_course_title_falls_back_to_registered_course_title_only_when_snapshots_are_missing(): void
    {
        [$result, $registeredCourse] = $this->releasedResult();
        $result->update(['course_title_snapshot' => null]);
        $registeredCourse->update(['course_title_snapshot' => null]);
        $registeredCourse->departmentCourse->studentCourse->update(['title' => 'Current Course Title']);

        $action = app(DisciplinaryActionService::class)->applySanction(
            $this->payload('course_cancellation', [
                'academic_session' => '2024/2025',
                'effective_session' => '2024/2025',
                'semester' => 'first',
                'result_id' => $result->id,
            ])
        );

        $audit = $action->auditEntries()->where('event', 'SANCTION_APPLIED')->firstOrFail();
        $this->assertSame('Current Course Title', $audit->metadata['course_snapshot']['course_title']);
    }

    public function test_active_repeat_sanction_overrides_promotion_for_its_effective_ug_session(): void
    {
        $session = app(AcademicSessionService::class)->getAcademicSession($this->student);
        ResultGpaRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'second',
            'academic_session' => $session,
            'semester_gpa' => 4.50,
            'cumulative_gpa' => 4.50,
            'total_credit_units' => 18,
            'total_grade_points' => 81,
            'cumulative_credit_units' => 36,
            'cumulative_grade_points' => 162,
        ]);
        app(DisciplinaryActionService::class)->applySanction($this->payload('repeat_session', [
            'academic_session' => $session,
            'effective_session' => $session,
        ]));

        $progression = app(AcademicProgressionService::class);
        $standing = $progression->determineAcademicStanding($this->student);

        $this->assertSame(AcademicProgressionService::STANDING_REPEAT, $standing['standing']);
        $this->assertSame($this->academicDetail->student_level_id, $progression->getNextEligibleLevel($this->student));
    }

    public function test_active_disciplinary_sanctions_are_included_in_broadsheets_and_transcripts(): void
    {
        [$result] = $this->releasedResult();
        app(DisciplinaryActionService::class)->applySanction($this->payload('repeat_session', [
            'academic_session' => '2024/2025',
            'effective_session' => '2024/2025',
            'semester' => 'first',
            'result_id' => $result->id,
        ]));

        $broadsheet = app(\App\Services\ResultReportingService::class)->getDepartmentalBroadsheet([
            'department_id' => $this->department->id,
            'academic_session' => '2024/2025',
            'semester' => 'first',
            'released_only' => true,
        ]);

        $this->assertNotEmpty($broadsheet['students']);
        $this->assertStringContainsString('REPEAT SESSION (SDC: SEN-2026-042)', $broadsheet['students'][0]['disciplinary_remarks'] ?? '');

        $transcript = app(\App\Services\TranscriptService::class)->buildTranscriptData($this->student);
        $this->assertSame('repeat_session', $transcript['disciplinaryAnnotation']['sanction_type']);
        $this->assertSame('SEN-2026-042', $transcript['disciplinaryAnnotation']['senate_reference']);
    }

    public function test_suspension_creates_authoritative_disciplinary_status_and_quash_closes_it(): void
    {
        $priorStatus = StudentStatusRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'status' => 'active',
            'status_type' => 'academic',
            'academic_session' => '2024/2025',
            'effective_date' => '2024-09-01',
            'senate_decision' => 'SENATE_APPROVED',
            'senate_reference' => 'SEN-2024-001',
        ]);
        $action = app(DisciplinaryActionService::class)->applySanction($this->payload('suspension', [
            'semester' => 'first',
            'effective_session' => '2026/2027',
            'resumption_session' => '2026/2027',
            'effective_date' => '2026-09-01',
            'end_date' => '2027-01-31',
        ]));
        $status = $action->studentStatusRecord;

        $this->assertNotNull($status);
        $this->assertSame('suspended', $status->status->value);
        $this->assertSame('disciplinary', $status->status_type->value);
        $this->assertSame(1, $status->semester);
        $this->assertSame('2027-01-31', $status->end_date->toDateString());

        app(DisciplinaryActionService::class)->liftSanction($action->id, 'SEN-2026-045');

        $this->assertSame(now()->toDateString(), $status->fresh()->end_date->toDateString());
        $this->assertDatabaseHas('student_status_audits', [
            'student_status_record_id' => $status->id,
            'action' => 'DISCIPLINARY_STATUS_ENDED',
        ]);
        $this->assertNull($priorStatus->fresh()->end_date);
        $this->assertDatabaseHas('student_status_audits', [
            'student_status_record_id' => $priorStatus->id,
            'action' => 'PRIOR_STATUS_REOPENED_AFTER_DISCIPLINARY_QUASH',
        ]);
    }

    public function test_expulsion_creates_permanent_status_until_the_senate_quashes_it(): void
    {
        $action = app(DisciplinaryActionService::class)->applySanction($this->payload('expulsion', [
            'effective_session' => '2026/2027',
            'effective_date' => '2026-09-20',
        ]));
        $status = $action->studentStatusRecord;

        $this->assertSame('expelled', $status->status->value);
        $this->assertNull($status->end_date);
        $this->assertFalse($status->reinstatement_eligible);

        app(DisciplinaryActionService::class)->liftSanction($action->id, 'SEN-2026-046');

        $this->assertNotNull($status->fresh()->end_date);
        $this->assertDatabaseHas('disciplinary_actions', [
            'id' => $action->id,
            'student_status_record_id' => $status->id,
            'is_active' => false,
            'appeal_status' => 'quashed',
        ]);
    }

    public function test_pg_student_cannot_receive_phase_seven_sanctions(): void
    {
        $pgStudent = $this->user('pg_student@test.com', 'student', 6);
        $pgDetail = AcademicDetail::create([
            'user_id' => $pgStudent->id,
            'matric_no' => 'PG/22/CS/1051',
            'course_id' => $this->programmeCourse->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2022/2023',
        ]);

        $this->expectException(AuthorizationException::class);
        app(DisciplinaryActionService::class)->applySanction($this->payload('expulsion', [
            'user_id' => $pgStudent->id,
            'academic_detail_id' => $pgDetail->id,
        ]));
    }

    public function test_staff_needs_the_dedicated_department_scoped_capability(): void
    {
        $staff = $this->user('disciplinary_staff@test.com', 'exam_officer', 7);
        $this->actingAs($staff);
        try {
            app(DisciplinaryActionService::class)->applySanction($this->payload('repeat_session'));
            $this->fail('Expected disciplinary capability check to deny staff without a grant.');
        } catch (AuthorizationException) {
            $this->assertDatabaseCount('disciplinary_actions', 0);
        }

        UserCapability::create([
            'user_id' => $staff->id,
            'capability' => 'disciplinary_actions.manage',
            'department_id' => $this->department->id,
            'is_active' => true,
            'granted_at' => now(),
        ]);

        $action = app(DisciplinaryActionService::class)->applySanction($this->payload('repeat_session'));
        $this->assertSame($staff->id, $action->sanctioned_by);
    }

    public function test_disciplinary_audit_rows_are_immutable(): void
    {
        $action = app(DisciplinaryActionService::class)->applySanction($this->payload('repeat_session'));
        $audit = $action->auditEntries()->firstOrFail();

        try {
            $audit->update(['event' => 'TAMPERED']);
            $this->fail('Expected immutable audit event update to throw.');
        } catch (LogicException $exception) {
            $this->assertSame('Disciplinary action audit entries are immutable.', $exception->getMessage());
        }
    }

    private function payload(string $type, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'sanction_type' => $type,
            'academic_session' => '2024/2025',
            'semester' => null,
            'senate_ref_no' => 'SEN-2026-042',
            'verdict_date' => '2026-09-20',
            'effective_session' => '2026/2027',
            'resumption_session' => null,
            'remarks' => 'Senate-approved disciplinary action.',
        ], $overrides);
    }

    private function user(string $email, string $role, int $programmeId): User
    {
        return User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $programmeId,
            'email' => $email,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => $role,
            'firstname' => 'Test',
            'surname' => 'User',
        ]);
    }

    /** @return array{Result, RegisteredCourse} */
    private function releasedResult(): array
    {
        $studentCourse = StudentCourse::create([
            'code' => 'CSC301', 'title' => 'Data Structures', 'units' => '3',
            'student_level_id' => $this->level->id, 'semester' => 1,
        ]);
        $departmentCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);
        $registeredCourse = RegisteredCourse::create([
            'academic_detail_id' => $this->academicDetail->id,
            'department_course_id' => $departmentCourse->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2024/2025',
            'course_code_snapshot' => 'CSC301',
            'course_title_snapshot' => 'Data Structures',
            'credit_units_snapshot' => 3,
            'semester_snapshot' => 'first',
            'level_snapshot' => 300,
        ]);
        $result = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $registeredCourse->id,
            'department_course_id' => $departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'course_code_snapshot' => 'CSC301',
            'course_title_snapshot' => 'Data Structures',
            'credit_units_snapshot' => 3,
            'semester_snapshot' => 'first',
            'level_snapshot' => 300,
            'semester' => 'first',
            'academic_session' => '2024/2025',
            'ca_score' => 35,
            'exam_score' => 50,
            'total_score' => 85,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'released',
            'remarks' => 'Original released result.',
        ]);

        return [$result, $registeredCourse];
    }
}
