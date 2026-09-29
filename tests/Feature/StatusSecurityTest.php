<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\StudentStatusAudit;
use App\Models\StudentStatusRecord;
use App\Models\User;
use App\Models\UserCapability;
use App\Services\StudentStatusService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LogicException;
use Tests\TestCase;

class StatusSecurityTest extends TestCase
{
    use RefreshDatabase;

    private StudentStatusService $service;
    private User $student;
    private User $admin;
    private User $hod;
    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StudentStatusService::class);

        $programme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate([
                'id' => ProgrammesEnum::Undergraduate->value,
                'name' => 'Undergraduate',
                'abv' => 'UG',
            ]);
        $this->department = Department::create(['name' => 'Computer Science']);
        $course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $programme->id,
            'semesters' => 8,
        ]);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '300']);

        $this->student = User::factory()->create(['role' => 'student', 'programme_id' => $programme->id]);
        AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'UG/STATUS/' . strtoupper(substr(sha1(uniqid('', true)), 0, 8)),
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2025/2026',
            'admission_session' => '2022/2023',
        ]);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->hod = User::factory()->create(['role' => 'hod']);
    }

    public function test_hod_without_explicit_capability_cannot_change_student_status(): void
    {
        try {
            $this->service->createWithdrawalRecommendation(
                $this->student,
                'TEST_REASON',
                'Test recommendation',
                '2025/2026',
                processedBy: $this->hod,
            );
            $this->fail('A HOD without a student-status capability must be denied.');
        } catch (AuthorizationException) {
            $this->assertDatabaseCount('student_status_records', 0);
            $this->assertDatabaseCount('student_status_audits', 0);
        }
    }

    public function test_department_scoped_capability_cannot_cross_departments(): void
    {
        $otherDepartment = Department::create(['name' => 'Mathematics']);
        $restrictedOfficer = User::factory()->create(['role' => 'hod']);
        UserCapability::create([
            'user_id' => $restrictedOfficer->id,
            'capability' => 'student_status.recommend',
            'department_id' => $otherDepartment->id,
            'is_active' => true,
        ]);

        $this->assertFalse(Gate::forUser($restrictedOfficer)->allows('student-status.recommend', $this->student));
        $this->expectException(AuthorizationException::class);
        $this->service->createWithdrawalRecommendation(
            $this->student,
            'TEST_REASON',
            'Test recommendation',
            '2025/2026',
            processedBy: $restrictedOfficer,
        );
    }

    public function test_capabilities_are_action_specific_and_status_decisions_are_audited(): void
    {
        UserCapability::create([
            'user_id' => $this->hod->id,
            'capability' => 'student_status.recommend',
            'is_active' => true,
        ]);

        $recommendation = $this->service->createWithdrawalRecommendation(
            $this->student,
            'TEST_REASON',
            'Test recommendation',
            '2025/2026',
            processedBy: $this->hod,
        );

        $this->assertDatabaseHas('student_status_audits', [
            'student_id' => $this->student->id,
            'actor_id' => $this->hod->id,
            'student_status_record_id' => $recommendation->id,
            'action' => 'WITHDRAWAL_RECOMMENDED',
            'old_status' => null,
            'new_status' => 'academic_withdrawal',
            'new_decision' => StudentStatusService::WORKFLOW_RECOMMENDED,
        ]);

        try {
            $this->service->submitForSenate($recommendation, $this->hod);
            $this->fail('Recommendation capability must not grant Senate submission.');
        } catch (AuthorizationException) {
            $this->assertSame(StudentStatusService::WORKFLOW_RECOMMENDED, $recommendation->fresh()->senate_decision);
        }

        $pending = $this->service->submitForSenate($recommendation, $this->admin);
        $approved = $this->service->approveWithdrawal(
            $pending,
            'SEN-2026-55',
            approvedBy: $this->admin,
        );

        $this->assertSame(StudentStatusService::WORKFLOW_SENATE_APPROVED, $approved->senate_decision);
        $this->assertDatabaseHas('student_status_audits', [
            'student_id' => $this->student->id,
            'actor_id' => $this->admin->id,
            'action' => 'WITHDRAWAL_APPROVED',
            'new_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'senate_reference' => 'SEN-2026-55',
        ]);
        $this->assertSame(3, StudentStatusAudit::where('student_id', $this->student->id)->count());
    }

    public function test_status_changes_require_an_authenticated_or_explicit_authorized_actor(): void
    {
        auth()->logout();

        $this->expectException(AuthorizationException::class);
        $this->service->createWithdrawalRecommendation(
            $this->student,
            'TEST_REASON',
            'Test recommendation',
            '2025/2026',
        );
    }

    public function test_authenticated_actor_cannot_be_replaced_by_a_different_user_argument(): void
    {
        $this->actingAs($this->hod);

        $this->expectException(AuthorizationException::class);
        $this->service->createWithdrawalRecommendation(
            $this->student,
            'TEST_REASON',
            'Test recommendation',
            '2025/2026',
            processedBy: $this->admin,
        );
    }

    public function test_senate_rejection_is_audited_with_its_reference(): void
    {
        $recommendation = $this->service->createWithdrawalRecommendation(
            $this->student,
            'TEST_REASON',
            'Test recommendation',
            '2025/2026',
            processedBy: $this->admin,
        );
        $pending = $this->service->submitForSenate($recommendation, $this->admin);

        $rejected = $this->service->rejectWithdrawal(
            $pending,
            'Senate rejected the recommendation.',
            rejectedBy: $this->admin,
            senateReference: 'SEN/2026/55',
        );

        $this->assertSame(StudentStatusService::WORKFLOW_SENATE_REJECTED, $rejected->senate_decision);
        $this->assertDatabaseHas('student_status_audits', [
            'student_id' => $this->student->id,
            'actor_id' => $this->admin->id,
            'action' => 'WITHDRAWAL_REJECTED',
            'senate_reference' => 'SEN/2026/55',
        ]);
    }

    public function test_audit_entries_cannot_be_updated_or_deleted(): void
    {
        $recommendation = $this->service->createWithdrawalRecommendation(
            $this->student,
            'TEST_REASON',
            'Test recommendation',
            '2025/2026',
            processedBy: $this->admin,
        );
        $audit = StudentStatusAudit::where('student_status_record_id', $recommendation->id)->firstOrFail();

        try {
            $audit->update(['action' => 'TAMPERED']);
            $this->fail('Audit entries must not be editable.');
        } catch (LogicException $exception) {
            $this->assertSame('Student status audit entries are immutable.', $exception->getMessage());
        }

        try {
            $audit->delete();
            $this->fail('Audit entries must not be deletable.');
        } catch (LogicException $exception) {
            $this->assertSame('Student status audit entries are immutable.', $exception->getMessage());
        }

        $this->assertDatabaseHas('student_status_audits', [
            'id' => $audit->id,
            'action' => 'WITHDRAWAL_RECOMMENDED',
        ]);
    }

    public function test_senate_reference_validation_rejects_malformed_values(): void
    {
        $this->assertTrue($this->service->isValidSenateReference('SEN-2026-55'));
        $this->assertTrue($this->service->isValidSenateReference('SEN/2026/104'));
        $this->assertTrue($this->service->isValidSenateReference('SEN-2025/2026-001'));
        $this->assertFalse($this->service->isValidSenateReference('SEN-26-55'));
        $this->assertFalse($this->service->isValidSenateReference('SEN-2026-55-extra'));
    }
}
