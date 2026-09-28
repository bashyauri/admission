<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AcademicActivity;
use App\Enums\ProgrammesEnum;
use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Http\Livewire\Student\CourseRegistration;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\GraduationEligibility;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\StudentStatusRecord;
use App\Models\StudentTransaction;
use App\Models\User;
use App\Services\CourseRegistrationService;
use App\Services\GraduationService;
use App\Services\PaymentService;
use App\Services\StudentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private StudentStatusService $statusService;
    private CourseRegistrationService $courseService;
    private PaymentService $paymentService;
    private GraduationService $graduationService;

    private Department $department;
    private Programme $ugProgramme;
    private Course $ugCourse;
    private StudentLevel $level100;
    private User $activeStudent;
    private User $withdrawnStudent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statusService = app(StudentStatusService::class);
        $this->courseService = app(CourseRegistrationService::class);
        $this->paymentService = app(PaymentService::class);
        $this->graduationService = app(GraduationService::class);

        $this->department = Department::create([
            'name' => 'Computer Science',
            'code' => 'CSC',
        ]);

        $this->ugProgramme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate([
                'id' => ProgrammesEnum::Undergraduate->value,
                'name' => 'B.Sc Computer Science',
                'abv' => 'UG',
            ]);

        $this->ugCourse = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $this->ugProgramme->id,
            'semesters' => 8,
        ]);

        $this->level100 = StudentLevel::forceCreate([
            'id' => 1,
            'level' => 100,
        ]);

        // Create active student
        $this->activeStudent = User::factory()->create([
            'role' => 'student',
            'programme_id' => $this->ugProgramme->id,
        ]);

        AcademicDetail::create([
            'user_id' => $this->activeStudent->id,
            'matric_no' => 'UG/2026/001',
            'department_id' => $this->department->id,
            'programme_id' => $this->ugProgramme->id,
            'student_level_id' => $this->level100->id,
            'acad_session' => '2025/2026',
            'course_id' => $this->ugCourse->id,
        ]);

        // Create withdrawn student
        $this->withdrawnStudent = User::factory()->create([
            'role' => 'student',
            'programme_id' => $this->ugProgramme->id,
        ]);

        $withdrawnDetail = AcademicDetail::create([
            'user_id' => $this->withdrawnStudent->id,
            'matric_no' => 'UG/2026/002',
            'department_id' => $this->department->id,
            'programme_id' => $this->ugProgramme->id,
            'student_level_id' => $this->level100->id,
            'acad_session' => '2025/2026',
            'course_id' => $this->ugCourse->id,
        ]);

        // Record Senate approved withdrawal for withdrawnStudent
        StudentStatusRecord::create([
            'user_id' => $this->withdrawnStudent->id,
            'academic_detail_id' => $withdrawnDetail->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'reason_code' => 'CONSECUTIVE_PROBATION',
            'reason' => 'Failed to meet CGPA requirement after two consecutive semesters on probation.',
            'academic_session' => '2025/2026',
            'effective_date' => '2026-01-15',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'senate_reference' => 'SEN-2026-104',
            'senate_decision_date' => '2026-01-15',
            'reinstatement_eligible' => true,
        ]);
    }

    public function test_active_student_can_perform_all_academic_activities(): void
    {
        foreach (AcademicActivity::cases() as $activity) {
            $this->assertTrue(
                $this->statusService->canPerformAcademicActivity($this->activeStudent, $activity),
                "Active student should be able to perform {$activity->value}"
            );
        }
    }

    public function test_withdrawn_student_is_blocked_from_all_academic_activities(): void
    {
        foreach (AcademicActivity::cases() as $activity) {
            $this->assertFalse(
                $this->statusService->canPerformAcademicActivity($this->withdrawnStudent, $activity),
                "Withdrawn student should be blocked from performing {$activity->value}"
            );
        }
    }

    public function test_course_registration_service_blocks_withdrawn_student_available_courses(): void
    {
        // Setup course catalog
        $studentCourse = StudentCourse::create([
            'code' => 'CSC101',
            'title' => 'Introduction to Computer Science',
            'units' => 3,
            'semester' => 1,
            'student_level_id' => $this->level100->id,
        ]);

        $deptCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);

        // Active student receives available courses
        $activeDetail = $this->activeStudent->academicDetail;
        $activeCourses = $this->courseService->getAvailableCourses(
            $this->department->id,
            $this->level100->id,
            $activeDetail->id,
            '2025/2026'
        );
        $this->assertCount(1, $activeCourses);

        // Withdrawn student receives empty collection
        $withdrawnDetail = $this->withdrawnStudent->academicDetail;
        $withdrawnCourses = $this->courseService->getAvailableCourses(
            $this->department->id,
            $this->level100->id,
            $withdrawnDetail->id,
            '2025/2026'
        );
        $this->assertCount(0, $withdrawnCourses);
    }

    public function test_course_registration_livewire_blocks_adding_courses_for_withdrawn_student(): void
    {
        $studentCourse = StudentCourse::create([
            'code' => 'CSC102',
            'title' => 'Programming Principles',
            'units' => 3,
            'semester' => 1,
            'student_level_id' => $this->level100->id,
        ]);

        $deptCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);

        $this->actingAs($this->withdrawnStudent);

        Livewire::test(CourseRegistration::class)
            ->call('addCourse', $deptCourse->id)
            ->assertDispatched('alert', function ($event, $params) {
                return str_contains($params['message'] ?? '', 'blocked');
            });

        // Ensure course was not registered
        $this->assertDatabaseMissing('registered_courses', [
            'academic_detail_id' => $this->withdrawnStudent->academicDetail->id,
            'department_course_id' => $deptCourse->id,
        ]);
    }

    public function test_course_registration_service_blocks_a_direct_registration_for_withdrawn_student(): void
    {
        $studentCourse = StudentCourse::create([
            'code' => 'CSC103',
            'title' => 'Data Structures',
            'units' => 3,
            'semester' => 1,
            'student_level_id' => $this->level100->id,
        ]);
        $departmentCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);

        try {
            $this->courseService->registerCourse(
                $this->withdrawnStudent->academicDetail,
                $departmentCourse,
                '2025/2026'
            );
            $this->fail('A withdrawn student must not be able to create a course registration.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('Academic Withdrawal', $exception->getMessage());
        }

        $this->assertDatabaseMissing('registered_courses', [
            'academic_detail_id' => $this->withdrawnStudent->academicDetail->id,
            'department_course_id' => $departmentCourse->id,
            'academic_session' => '2025/2026',
        ]);
    }

    public function test_payment_service_blocks_fee_invoice_generation_for_withdrawn_student(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Fee invoice generation is blocked due to current student institutional status.');

        $this->paymentService->createPayment([
            'user_id' => $this->withdrawnStudent->id,
            'amount' => 50000,
            'transactionId' => 'TXN-TEST-001',
            'description' => 'Undergraduate School Fees',
        ]);
    }

    public function test_payment_service_blocks_before_sending_a_remita_invoice_request(): void
    {
        Http::fake();

        try {
            $this->paymentService->generateInvoice([
                'user_id' => $this->withdrawnStudent->id,
                'amount' => 50000,
                'transactionId' => 'TXN-BLOCKED-001',
                'payerName' => 'Withdrawn Student',
                'payerEmail' => 'withdrawn@example.test',
                'payerPhone' => '08000000000',
                'description' => 'Undergraduate School Fees',
                'apiHash' => 'test-hash',
            ]);
            $this->fail('Fee invoice generation should be denied before contacting Remita.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('Academic Withdrawal', $exception->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_graduation_service_flags_withdrawn_status_deficiency(): void
    {
        $result = $this->graduationService->checkEligibility($this->withdrawnStudent, '2025/2026');

        $this->assertFalse($result['eligible']);
        $this->assertNotEmpty($result['deficiencies']);
        $this->assertTrue(collect($result['deficiencies'])->contains(function ($d) {
            return str_contains($d, 'not academically active') && str_contains($d, 'Academic Withdrawal');
        }));
    }

    public function test_graduation_clearance_and_staging_recheck_current_student_status(): void
    {
        $result = $this->graduationService->checkEligibility($this->withdrawnStudent, '2025/2026');
        $eligibility = GraduationEligibility::findOrFail($result['eligibility_id']);
        $eligibility->update(['meets_requirements' => true, 'is_cleared' => true]);
        $officer = User::factory()->create(['role' => 'exam_officer']);

        try {
            $this->graduationService->clearStudent($eligibility, $officer);
            $this->fail('A withdrawn student must not be cleared for graduation.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('Academic Withdrawal', $exception->getMessage());
        }

        try {
            $this->graduationService->addToGraduationList($eligibility, '2025/2026');
            $this->fail('A withdrawn student must not be staged for graduation.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('Academic Withdrawal', $exception->getMessage());
        }

        $this->assertDatabaseMissing('graduation_lists', ['academic_session' => '2025/2026']);
    }

    public function test_postgraduate_payment_is_not_blocked_by_ug_status_enforcement(): void
    {
        $pgProgramme = Programme::find(ProgrammesEnum::PG->value)
            ?? Programme::forceCreate(['id' => ProgrammesEnum::PG->value, 'name' => 'MSc Computer Science', 'abv' => 'PG']);
        $pgStudent = User::factory()->create([
            'role' => 'student',
            'programme_id' => $pgProgramme->id,
        ]);
        StudentStatusRecord::create([
            'user_id' => $pgStudent->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2025/2026',
            'effective_date' => '2026-01-15',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
        ]);

        $this->assertTrue($this->statusService->canPerformAcademicActivity($pgStudent, AcademicActivity::SCHOOL_FEES));

        Http::fake();
        $payment = $this->paymentService->createPayment([
            'user_id' => $pgStudent->id,
            'student_level_id' => $this->level100->id,
            'amount' => 25000,
            'transactionId' => 'PG-TXN-ALLOW-001',
            'RRR' => 'PG-RRR-ALLOW-001',
            'description' => 'Postgraduate Fee',
        ]);

        $this->assertNotNull($payment);
        $this->assertSame($pgStudent->id, $payment->user_id);
        Http::assertNothingSent();
    }

    public function test_historical_records_remain_accessible_for_withdrawn_student(): void
    {
        // 1. Historical registered courses
        $studentCourse = StudentCourse::create([
            'code' => 'CSC100',
            'title' => 'Computer Basics',
            'units' => 2,
            'semester' => 1,
            'student_level_id' => $this->level100->id,
        ]);

        $deptCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 2,
        ]);

        RegisteredCourse::create([
            'academic_detail_id' => $this->withdrawnStudent->academicDetail->id,
            'department_course_id' => $deptCourse->id,
            'semester' => 1,
            'units' => 2,
            'student_level_id' => $this->level100->id,
            'academic_session' => '2024/2025',
        ]);

        $registered = $this->courseService->getRegisteredCourses($this->withdrawnStudent->academicDetail->id, '2024/2025');
        $this->assertCount(1, $registered);

        // 2. Historical payment transactions
        StudentTransaction::create([
            'transaction_id' => 'HIST-TXN-001',
            'user_id' => $this->withdrawnStudent->id,
            'student_levels_id' => $this->level100->id,
            'amount' => '45000',
            'date' => '2024-10-01',
            'status' => '00',
            'resource' => 'Undergraduate School Fees',
            'acad_session' => '2024/2025',
        ]);

        $payments = $this->paymentService->getStudentPayments($this->withdrawnStudent->id);
        $this->assertCount(1, $payments);

        $this->actingAs($this->withdrawnStudent);
        Livewire::test(CourseRegistration::class)
            ->call('deleteCourse', $registered->first()->id)
            ->assertDispatched('alert', function ($event, $params) {
                return str_contains($params['message'] ?? '', 'institutional status');
            });
        $this->assertDatabaseHas('registered_courses', ['id' => $registered->first()->id]);
    }

    public function test_reinstated_student_regains_activity_authorization(): void
    {
        // Reinstate student
        StudentStatusRecord::create([
            'user_id' => $this->withdrawnStudent->id,
            'academic_detail_id' => $this->withdrawnStudent->academicDetail->id,
            'status' => StudentStatus::REINSTATED,
            'status_type' => StudentStatusType::ACADEMIC,
            'reason_code' => 'REINSTATEMENT',
            'reason' => 'Reinstated by Senate after appeal.',
            'academic_session' => '2025/2026',
            'effective_date' => '2026-03-01',
            'senate_decision' => StudentStatusService::WORKFLOW_SENATE_APPROVED,
            'senate_reference' => 'SEN-2026-200',
            'senate_decision_date' => '2026-03-01',
            'reinstatement_eligible' => false,
        ]);

        $this->assertTrue(
            $this->statusService->canPerformAcademicActivity($this->withdrawnStudent, AcademicActivity::COURSE_REGISTRATION),
            'Reinstated student should be authorized to register courses'
        );

        $this->assertTrue(
            $this->statusService->canPerformAcademicActivity($this->withdrawnStudent, AcademicActivity::SCHOOL_FEES),
            'Reinstated student should be authorized for fee payment invoice'
        );
    }
}
