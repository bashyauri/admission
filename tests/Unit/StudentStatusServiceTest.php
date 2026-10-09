<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ProgrammesEnum;
use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\StudentStatusRecord;
use App\Models\User;
use App\Services\StudentStatusService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private StudentStatusService $service;
    private User $admin;
    private Department $department;
    private Programme $ugProgramme;
    private Programme $pgProgramme;
    private Course $ugCourse;
    private Course $pgCourse;
    private StudentLevel $level300;
    private User $undergraduateUser;
    private User $postgraduateUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(StudentStatusService::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);

        $this->department = Department::create(['name' => 'Computer Science']);

        $this->ugProgramme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate(['name' => 'B.Sc Computer Science', 'abv' => 'UG', 'id' => ProgrammesEnum::Undergraduate->value]);

        $this->pgProgramme = Programme::find(ProgrammesEnum::PG->value)
            ?? Programme::forceCreate(['name' => 'M.Sc Computer Science', 'abv' => 'PG', 'id' => ProgrammesEnum::PG->value]);

        $this->ugCourse = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $this->ugProgramme->id,
            'semesters' => 8,
        ]);

        $this->pgCourse = Course::create([
            'name' => 'Computer Science (PG)',
            'department_id' => $this->department->id,
            'programme_id' => $this->pgProgramme->id,
        ]);

        $this->level300 = StudentLevel::where('level', '300')->first()
            ?? StudentLevel::create(['level' => '300']);

        $this->undergraduateUser = $this->makeUgStudent('ug_main@student.com');
        $this->attachAcademicDetail($this->undergraduateUser, $this->ugCourse, $this->level300);

        $this->postgraduateUser = $this->makePgStudent('pg_main@student.com');
        $this->attachAcademicDetail($this->postgraduateUser, $this->pgCourse, $this->level300);
    }

    private function makeUgStudent(?string $email = null): User
    {
        return User::create([
            'id' => (string) Str::uuid(),
            'surname' => 'Test',
            'firstname' => 'UG',
            'm_name' => 'Student',
            'email' => $email ?? 'ug_' . rand(1000, 9999) . '@student.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'student',
            'programme_id' => $this->ugProgramme->id,
        ]);
    }

    private function makePgStudent(?string $email = null): User
    {
        return User::create([
            'id' => (string) Str::uuid(),
            'surname' => 'Test',
            'firstname' => 'PG',
            'm_name' => 'Student',
            'email' => $email ?? 'pg_' . rand(1000, 9999) . '@student.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'student',
            'programme_id' => $this->pgProgramme->id,
        ]);
    }

    private function attachAcademicDetail(User $user, Course $course, StudentLevel $level, string $admissionSession = '2021/2022'): AcademicDetail
    {
        return AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/' . rand(10000, 99999),
            'course_id' => $course->id,
            'programme_id' => $user->programme_id,
            'department_id' => $this->department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2024/2025',
            'admission_session' => $admissionSession,
        ]);
    }

    // =========================================================================
    // Assessment Gateway Tests
    // =========================================================================

    public function test_evaluate_academic_withdrawal_for_undergraduate_student(): void
    {
        $result = $this->service->evaluateAcademicWithdrawal($this->undergraduateUser);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('eligible', $result);
        $this->assertArrayHasKey('reason_code', $result);
        $this->assertArrayHasKey('reason', $result);
        $this->assertArrayHasKey('standing', $result);
        $this->assertArrayHasKey('cgpa', $result);
        $this->assertArrayHasKey('triggered_rules', $result);
        $this->assertIsBool($result['eligible']);
        $this->assertIsArray($result['triggered_rules']);
    }

    public function test_evaluate_academic_withdrawal_excludes_postgraduate_students(): void
    {
        $result = $this->service->evaluateAcademicWithdrawal($this->postgraduateUser);

        $this->assertFalse($result['eligible']);
        $this->assertNull($result['reason_code']);
        $this->assertStringContainsString('Postgraduate', $result['reason']);
    }

    // =========================================================================
    // Status Query Tests (getCurrentStatus, getStatusHistory, getStatusForSession)
    // =========================================================================

    public function test_get_current_status_returns_null_for_new_student(): void
    {
        $newUser = $this->makeUgStudent();
        $this->attachAcademicDetail($newUser, $this->ugCourse, $this->level300);

        $status = $this->service->getCurrentStatus($newUser);

        $this->assertNull($status);
    }

    public function test_get_current_status_returns_active_status(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACTIVE,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2024-01-01',
        ]);

        $status = $this->service->getCurrentStatus($this->undergraduateUser);

        $this->assertNotNull($status);
        $this->assertEquals(StudentStatus::ACTIVE, $status->status);
    }

    public function test_get_current_status_ignores_rejected_records(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_REJECTED,
            'effective_date' => '2024-01-01',
        ]);

        $status = $this->service->getCurrentStatus($this->undergraduateUser);

        $this->assertNull($status);
    }

    public function test_get_current_status_ignores_expired_records(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2023/2024',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2023-01-01',
            'end_date' => '2023-12-31',
        ]);

        $status = $this->service->getCurrentStatus($this->undergraduateUser);

        $this->assertNull($status);
    }

    public function test_get_current_status_respects_target_date(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2025/2026',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2025-09-01',
        ]);

        // Before effective date -> null
        $this->assertNull($this->service->getCurrentStatus($this->undergraduateUser, '2025-08-01'));

        // On or after effective date -> returned
        $status = $this->service->getCurrentStatus($this->undergraduateUser, '2025-09-15');
        $this->assertNotNull($status);
        $this->assertEquals(StudentStatus::ACADEMIC_WITHDRAWAL, $status->status);
    }

    public function test_get_status_for_session_retrieves_matching_record(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2024/2025',
            'semester' => 1,
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2024-09-01',
        ]);

        $found = $this->service->getStatusForSession($this->undergraduateUser, '2024/2025', 1);
        $this->assertNotNull($found);
        $this->assertEquals(StudentStatus::ACADEMIC_WITHDRAWAL, $found->status);

        $notFound = $this->service->getStatusForSession($this->undergraduateUser, '2023/2024');
        $this->assertNull($notFound);
    }

    public function test_get_status_history_returns_chronological_timeline(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACTIVE,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2023/2024',
            'effective_date' => '2024-01-01',
        ]);

        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2023/2024',
            'effective_date' => '2024-06-01',
        ]);

        $history = $this->service->getStatusHistory($this->undergraduateUser);

        $this->assertCount(2, $history);
        $this->assertEquals('2024-01-01', $history->first()->effective_date->toDateString());
        $this->assertEquals('2024-06-01', $history->last()->effective_date->toDateString());
    }

    // =========================================================================
    // Activity Status Tests (isAcademicallyActive, isEligibleForReinstatement)
    // =========================================================================

    public function test_is_academically_active_returns_true_for_new_student(): void
    {
        $newUser = $this->makeUgStudent();
        $this->assertTrue($this->service->isAcademicallyActive($newUser));
    }

    public function test_is_academically_active_returns_true_for_active_status(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACTIVE,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2024-01-01',
        ]);

        $this->assertTrue($this->service->isAcademicallyActive($this->undergraduateUser));
    }

    public function test_is_academically_active_returns_true_for_reinstated_status(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::REINSTATED,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2024-01-01',
        ]);

        $this->assertTrue($this->service->isAcademicallyActive($this->undergraduateUser));
    }

    public function test_is_academically_active_returns_false_for_withdrawn_status(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2024-01-01',
        ]);

        $this->assertFalse($this->service->isAcademicallyActive($this->undergraduateUser));
    }

    public function test_is_academically_active_returns_false_for_suspended_status(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::SUSPENDED,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2024-01-01',
        ]);

        $this->assertFalse($this->service->isAcademicallyActive($this->undergraduateUser));
    }

    public function test_is_eligible_for_reinstatement_returns_false_for_active_student(): void
    {
        $this->assertFalse($this->service->isEligibleForReinstatement($this->undergraduateUser));
    }

    public function test_is_eligible_for_reinstatement_returns_true_when_flag_set(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2024-01-01',
            'reinstatement_eligible' => true,
        ]);

        $this->assertTrue($this->service->isEligibleForReinstatement($this->undergraduateUser));
    }

    public function test_is_eligible_for_reinstatement_returns_false_when_flag_not_set(): void
    {
        StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::SUSPENDED,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2024-01-01',
            'reinstatement_eligible' => false,
        ]);

        $this->assertFalse($this->service->isEligibleForReinstatement($this->undergraduateUser));
    }

    // =========================================================================
    // Senate Workflow Tests: Recommendations
    // =========================================================================

    public function test_create_withdrawal_recommendation_creates_record(): void
    {
        config(['academic_withdrawal.bypass_senate' => true]);

        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: '2 consecutive sessions of probation',
            academicSession: '2024/2025',
            semester: 1,
            notes: 'Reviewed by Faculty Board'
        );

        $this->assertInstanceOf(StudentStatusRecord::class, $record);
        $this->assertEquals(StudentStatus::ACADEMIC_WITHDRAWAL_PROGRAM, $record->status);
        $this->assertEquals(StudentStatusType::ACADEMIC, $record->status_type);
        $this->assertEquals(StudentStatusService::WORKFLOW_RECOMMENDED, $record->senate_decision);
        $this->assertEquals('CONSECUTIVE_PROBATION', $record->reason_code);
        $this->assertEquals('2024/2025', $record->academic_session);
        $this->assertEquals(1, $record->semester);
        $this->assertTrue($record->reinstatement_eligible);
    }

    public function test_create_withdrawal_recommendation_updates_progression_record(): void
    {
        $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: '2 consecutive sessions of probation',
            academicSession: '2024/2025',
            semester: 2
        );

        $progression = AcademicProgressionRecord::where('user_id', $this->undergraduateUser->id)
            ->where('academic_session', '2024/2025')
            ->where('semester', 2)
            ->first();

        $this->assertNotNull($progression);
        $this->assertTrue($progression->withdrawal_recommended);
    }

    public function test_create_withdrawal_recommendation_throws_for_postgraduate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Withdrawal recommendations are only applicable to undergraduate students.');

        $this->service->createWithdrawalRecommendation(
            user: $this->postgraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test reason',
            academicSession: '2024/2025'
        );
    }

    public function test_create_withdrawal_recommendation_validates_session_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid academic session format: 2024-2025');

        $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test reason',
            academicSession: '2024-2025'
        );
    }

    public function test_create_withdrawal_recommendation_validates_semester(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Semester must be 1 or 2.');

        $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test reason',
            academicSession: '2024/2025',
            semester: 3
        );
    }

    // =========================================================================
    // Senate Workflow Tests: Submission
    // =========================================================================

    public function test_submit_for_senate_updates_workflow_state(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025'
        );

        $submitted = $this->service->submitForSenate($record);

        $this->assertEquals(StudentStatusService::WORKFLOW_PENDING_SENATE, $submitted->senate_decision);
    }

    public function test_submit_for_senate_only_works_on_recommendations(): void
    {
        $record = StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'academic_session' => '2024/2025',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only recommendations can be submitted to Senate.');

        $this->service->submitForSenate($record);
    }

    // =========================================================================
    // Senate Workflow Tests: Approval
    // =========================================================================

    public function test_approve_withdrawal_updates_to_senate_approved(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025'
        );

        $submitted = $this->service->submitForSenate($record);

        $approved = $this->service->approveWithdrawal(
            recommendation: $submitted,
            senateReference: 'SEN-2025-101',
            senateDecisionDate: '2025-03-15',
            senateDecisionDetails: 'Approved by Senate 302nd meeting'
        );

        $this->assertEquals(StudentStatusService::WORKFLOW_SENATE_APPROVED, $approved->senate_decision);
        $this->assertEquals('SEN-2025-101', $approved->senate_reference);
        $this->assertEquals('2025-03-15', $approved->senate_decision_date->toDateString());
        $this->assertEquals('Approved by Senate 302nd meeting', $approved->notes);
    }

    public function test_approve_withdrawal_closes_previous_active_status(): void
    {
        $activeRecord = StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACTIVE,
            'academic_session' => '2023/2024',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2023-09-01',
            'end_date' => null,
        ]);

        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025'
        );

        $submitted = $this->service->submitForSenate($record);

        $this->service->approveWithdrawal(
            recommendation: $submitted,
            senateReference: 'SEN-2025-102',
            senateDecisionDate: '2025-03-15',
            effectiveDate: '2025-03-15'
        );

        $activeRecord->refresh();
        $this->assertEquals('2025-03-15', $activeRecord->end_date->toDateString());
    }

    public function test_approve_withdrawal_validates_senate_reference(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025'
        );

        $submitted = $this->service->submitForSenate($record);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Senate reference format: INVALID_FORMAT');

        $this->service->approveWithdrawal(
            recommendation: $submitted,
            senateReference: 'INVALID_FORMAT'
        );
    }

    public function test_approve_withdrawal_only_works_on_pending_senate(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only pending Senate decisions can be approved.');

        $this->service->approveWithdrawal(
            recommendation: $record,
            senateReference: 'SEN-2025-103'
        );
    }

    // =========================================================================
    // Senate Workflow Tests: Rejection
    // =========================================================================

    public function test_reject_withdrawal_updates_to_senate_rejected(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025'
        );

        $submitted = $this->service->submitForSenate($record);

        $rejected = $this->service->rejectWithdrawal(
            recommendation: $submitted,
            rejectionReason: 'Student granted compassionate waiver by Senate',
            senateDecisionDate: '2025-03-15'
        );

        $this->assertEquals(StudentStatusService::WORKFLOW_SENATE_REJECTED, $rejected->senate_decision);
        $this->assertEquals('Student granted compassionate waiver by Senate', $rejected->notes);
        $this->assertEquals('2025-03-15', $rejected->senate_decision_date->toDateString());
    }

    public function test_reject_withdrawal_clears_progression_withdrawal_flag(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025',
            semester: 1
        );

        $submitted = $this->service->submitForSenate($record);

        $this->service->rejectWithdrawal(
            recommendation: $submitted,
            rejectionReason: 'Compassionate grounds'
        );

        $progression = AcademicProgressionRecord::where('user_id', $this->undergraduateUser->id)
            ->where('academic_session', '2024/2025')
            ->where('semester', 1)
            ->first();

        $this->assertNotNull($progression);
        $this->assertFalse((bool) $progression->withdrawal_recommended);
    }

    public function test_reject_withdrawal_only_works_on_pending_senate(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only pending Senate decisions can be rejected.');

        $this->service->rejectWithdrawal(
            recommendation: $record,
            rejectionReason: 'Not pending'
        );
    }

    // =========================================================================
    // Voluntary & Medical Withdrawal Tests
    // =========================================================================

    public function test_process_voluntary_withdrawal_creates_record(): void
    {
        $record = $this->service->processVoluntaryWithdrawal(
            user: $this->undergraduateUser,
            reason: 'Relocating abroad',
            academicSession: '2024/2025'
        );

        $this->assertEquals(StudentStatus::VOLUNTARY_WITHDRAWAL, $record->status);
        $this->assertEquals(StudentStatusType::VOLUNTARY, $record->status_type);
        $this->assertEquals(StudentStatusService::WORKFLOW_RECOMMENDED, $record->senate_decision);
        $this->assertTrue($record->reinstatement_eligible);
    }

    public function test_process_voluntary_withdrawal_with_senate_reference(): void
    {
        $activeRecord = StudentStatusRecord::create([
            'user_id' => $this->undergraduateUser->id,
            'status' => StudentStatus::ACTIVE,
            'academic_session' => '2023/2024',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'effective_date' => '2023-09-01',
        ]);

        $record = $this->service->processVoluntaryWithdrawal(
            user: $this->undergraduateUser,
            reason: 'Personal reasons',
            academicSession: '2024/2025',
            effectiveDate: '2025-01-10',
            senateReference: 'SEN-2025-105'
        );

        $this->assertEquals(StudentStatusService::WORKFLOW_SENATE_APPROVED, $record->senate_decision);
        $this->assertEquals('SEN-2025-105', $record->senate_reference);

        $activeRecord->refresh();
        $this->assertEquals('2025-01-10', $activeRecord->end_date->toDateString());
    }

    public function test_process_voluntary_withdrawal_throws_for_postgraduate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Voluntary withdrawal is only applicable to undergraduate students.');

        $this->service->processVoluntaryWithdrawal(
            user: $this->postgraduateUser,
            reason: 'Personal',
            academicSession: '2024/2025'
        );
    }

    public function test_process_medical_withdrawal_creates_record(): void
    {
        $record = $this->service->processMedicalWithdrawal(
            user: $this->undergraduateUser,
            reason: 'Extended medical treatment',
            academicSession: '2024/2025'
        );

        $this->assertEquals(StudentStatus::MEDICAL_WITHDRAWAL, $record->status);
        $this->assertEquals(StudentStatusType::MEDICAL, $record->status_type);
        $this->assertEquals(StudentStatusService::WORKFLOW_RECOMMENDED, $record->senate_decision);
        $this->assertTrue($record->reinstatement_eligible);
    }

    public function test_process_medical_withdrawal_with_senate_reference(): void
    {
        $record = $this->service->processMedicalWithdrawal(
            user: $this->undergraduateUser,
            reason: 'Medical board certified',
            academicSession: '2024/2025',
            effectiveDate: '2025-02-01',
            senateReference: 'SEN/2025/201'
        );

        $this->assertEquals(StudentStatusService::WORKFLOW_SENATE_APPROVED, $record->senate_decision);
        $this->assertEquals('SEN/2025/201', $record->senate_reference);
    }

    // =========================================================================
    // Format Validation & Date Tests
    // =========================================================================

    public function test_valid_senate_reference_formats(): void
    {
        $this->assertTrue($this->service->isValidSenateReference('SEN-2026-104'));
        $this->assertTrue($this->service->isValidSenateReference('SEN/2026/104'));
        $this->assertTrue($this->service->isValidSenateReference('SEN-2025/2026-001'));
        $this->assertTrue($this->service->isValidSenateReference('sen-2026-55'));
    }

    public function test_invalid_senate_reference_formats(): void
    {
        $this->assertFalse($this->service->isValidSenateReference('INVALID_REF'));
        $this->assertFalse($this->service->isValidSenateReference('123456'));
        $this->assertFalse($this->service->isValidSenateReference('SEN-ABC-XYZ'));
        $this->assertFalse($this->service->isValidSenateReference('SEN-20-1'));
        $this->assertFalse($this->service->isValidSenateReference(''));
    }

    public function test_valid_academic_session_formats(): void
    {
        $this->assertTrue($this->service->isValidAcademicSession('2024/2025'));
        $this->assertTrue($this->service->isValidAcademicSession('2025/2026'));
        $this->assertFalse($this->service->isValidAcademicSession('2024-2025'));
        $this->assertFalse($this->service->isValidAcademicSession('2024'));
        $this->assertFalse($this->service->isValidAcademicSession(''));
    }

    public function test_effective_date_accepts_carbon_instance(): void
    {
        $carbonDate = Carbon::create(2025, 4, 15);

        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025',
            effectiveDate: $carbonDate
        );

        $this->assertEquals('2025-04-15', $record->effective_date->toDateString());
    }

    public function test_effective_date_accepts_string(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025',
            effectiveDate: '2025-05-20'
        );

        $this->assertEquals('2025-05-20', $record->effective_date->toDateString());
    }

    public function test_effective_date_defaults_to_now_when_null(): void
    {
        $record = $this->service->createWithdrawalRecommendation(
            user: $this->undergraduateUser,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Test',
            academicSession: '2024/2025'
        );

        $this->assertEquals(now()->toDateString(), $record->effective_date->toDateString());
    }
}
