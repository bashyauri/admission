<?php

namespace Tests\Unit;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\Approval;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\Coordinator;
use App\Models\Department;
use App\Models\DepartmentMaxUnit;
use App\Models\DepartmentCourse;
use App\Models\HodUser;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\CarryOverRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarryOverRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected CarryOverRegistrationService $carryOverService;
    protected User $student;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
    protected StudentCourse $studentCourse;
    protected DepartmentCourse $deptCourse;
    protected AcademicDetail $academicDetail;
    protected StudentLevel $level;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carryOverService = new CarryOverRegistrationService();

        $this->department = Department::first() ?? new Department();
        if (!$this->department->exists) {
            $this->department->name = 'CS Dept ' . rand(100, 999);
            $this->department->save();
        }

        $this->programme = Programme::first() ?? new Programme();
        if (!$this->programme->exists) {
            $this->programme->name = 'B.Sc CS ' . rand(100, 999);
            $this->programme->abv = 'CS';
            $this->programme->save();
        }

        $this->student = new User();
        $this->student->id = (string) \Illuminate\Support\Str::uuid();
        $this->student->programme_id = $this->programme->id;
        $this->student->email = 'student' . rand(1000, 9999) . '@test.com';
        $this->student->password = bcrypt('password');
        $this->student->vpassword = 'password';
        $this->student->role = 'student';
        $undergraduateProgramme = Programme::find(ProgrammesEnum::Undergraduate->value);
        if (!$undergraduateProgramme) {
            $undergraduateProgramme = new Programme();
            $undergraduateProgramme->id = ProgrammesEnum::Undergraduate->value;
            $undergraduateProgramme->name = 'Undergraduate';
            $undergraduateProgramme->abv = 'UG';
            $undergraduateProgramme->save();
        }
        $this->student->programme_id = ProgrammesEnum::Undergraduate->value;
        $this->student->save();

        $this->course = new Course();
        $this->course->name = 'CSC101';
        $this->course->department_id = $this->department->id;
        $this->course->programme_id = $this->programme->id;
        $this->course->save();

        $this->level = StudentLevel::first() ?? new StudentLevel();
        if (!$this->level->exists) {
            $this->level->level = '100';
            $this->level->save();
        }

        $this->studentCourse = new StudentCourse();
        $this->studentCourse->code = 'CSC101' . rand(10, 99);
        $this->studentCourse->title = 'Intro to Programming ' . rand(10, 99);
        $this->studentCourse->units = '3';
        $this->studentCourse->student_level_id = $this->level->id;
        $this->studentCourse->semester = 1;
        $this->studentCourse->save();

        $this->deptCourse = new DepartmentCourse();
        $this->deptCourse->department_id = $this->department->id;
        $this->deptCourse->student_course_id = $this->studentCourse->id;
        $this->deptCourse->units = 3;
        $this->deptCourse->save();

        $this->academicDetail = new AcademicDetail();
        $this->academicDetail->user_id = $this->student->id;
        $this->academicDetail->matric_no = 'MAT/' . rand(1000, 9999);
        $this->academicDetail->course_id = $this->course->id;
        $this->academicDetail->programme_id = $this->programme->id;
        $this->academicDetail->department_id = $this->department->id;
        $this->academicDetail->student_level_id = $this->level->id;
        $this->academicDetail->acad_session = '2023-2024';
        $this->academicDetail->save();

        $coordinator = Coordinator::create([
            'user_id' => $this->student->id,
            'department_id' => $this->department->id,
        ]);
        Approval::create([
            'academic_detail_id' => $this->academicDetail->id,
            'coordinator_id' => $coordinator->id,
            'pin' => 'PIN-' . rand(1000, 9999),
            'is_used' => true,
            'approval_status' => 'Approved',
        ]);
        \App\Models\Setting::setValue('ACADEMIC_SESSION', '2023/2024');
    }

    public function test_records_carry_over_on_failed_result(): void
    {
        $reg = RegisteredCourse::create([
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2023-2024',
        ]);

        $failedResult = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2023-2024',
            'ca_score' => 15.0,
            'exam_score' => 20.0,
            'total_score' => 35.0,
            'grade' => 'F',
            'grade_point' => 0,
            'credit_units' => 3,
            'status' => 'released',
        ]);

        $carryOver = $this->carryOverService->recordFailedCourse($failedResult);

        $this->assertNotNull($carryOver);
        $this->assertFalse($carryOver->is_cleared);
        $this->assertEquals('F', $carryOver->failed_grade);
        $this->assertEquals(35.0, (float) $carryOver->failed_score);

        $activeCarryOvers = $this->carryOverService->getActiveCarryOvers($this->student);
        $this->assertCount(1, $activeCarryOvers);
        $this->assertSame(1, RegisteredCourse::where('academic_detail_id', $this->academicDetail->id)->count(), 'A failed result release must not register a retake immediately.');
    }

    public function test_clears_carry_over_on_retake_passing_grade(): void
    {
        $reg1 = RegisteredCourse::create([
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2023-2024',
        ]);

        // 1. Initial Failure
        $failedResult = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg1->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2023-2024',
            'ca_score' => 10.0,
            'exam_score' => 20.0,
            'total_score' => 30.0,
            'grade' => 'F',
            'grade_point' => 0,
            'credit_units' => 3,
            'status' => 'released',
        ]);
        $this->carryOverService->recordFailedCourse($failedResult);

        // 2. Retake Next Session (2024-2025)
        $passedResult = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg1->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'ca_score' => 25.0,
            'exam_score' => 45.0,
            'total_score' => 70.0,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'is_repeated' => true,
            'original_result_id' => $failedResult->id,
            'status' => 'released',
        ]);

        $cleared = $this->carryOverService->processResultClearance($passedResult);
        $this->assertTrue($cleared);

        $carryOverRecord = CarryOverCourse::where('user_id', $this->student->id)
            ->where('department_course_id', $this->deptCourse->id)
            ->first();

        $this->assertTrue($carryOverRecord->is_cleared);
        $this->assertEquals($passedResult->id, $carryOverRecord->cleared_result_id);
        $this->assertEquals('2024-2025', $carryOverRecord->retake_session);

        $activeCarryOvers = $this->carryOverService->getActiveCarryOvers($this->student);
        $this->assertCount(0, $activeCarryOvers);
    }

    public function test_eligible_carry_over_is_registered_once_with_current_offering_snapshots(): void
    {
        $originalRegistration = RegisteredCourse::create([
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2023-2024',
            'course_code_snapshot' => 'OLD101',
            'course_title_snapshot' => 'Old title',
            'credit_units_snapshot' => 3,
            'semester_snapshot' => '1',
            'level_snapshot' => 100,
        ]);
        $failedResult = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $originalRegistration->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2023-2024',
            'total_score' => 35,
            'grade' => 'F',
            'credit_units' => 3,
            'status' => 'released',
        ]);
        $carryOver = $this->carryOverService->recordFailedCourse($failedResult);

        $this->studentCourse->title = 'Updated offering title';
        $this->studentCourse->code = 'NEW101';
        $this->studentCourse->save();
        $this->deptCourse->units = 5;
        $this->deptCourse->save();

        $this->carryOverService->registerEligibleRetakes($this->student, $this->academicDetail);
        $this->assertSame(1, RegisteredCourse::where('academic_detail_id', $this->academicDetail->id)->count(), 'Same-session processing must not retake the failed course immediately.');

        \App\Models\Setting::setValue('ACADEMIC_SESSION', '2024/2025');
        $this->carryOverService->registerEligibleRetakes($this->student, $this->academicDetail);
        $this->carryOverService->registerEligibleRetakes($this->student, $this->academicDetail);

        $retake = RegisteredCourse::where('academic_detail_id', $this->academicDetail->id)
            ->where('academic_session', '2024/2025')
            ->sole();
        $this->assertSame('NEW101', $retake->course_code_snapshot);
        $this->assertSame('Updated offering title', $retake->course_title_snapshot);
        $this->assertSame(5, (int) $retake->credit_units_snapshot);
        $this->assertSame('OLD101', $originalRegistration->fresh()->course_code_snapshot);
        $this->assertSame('Old title', $originalRegistration->fresh()->course_title_snapshot);
        $this->assertSame($retake->id, $carryOver->fresh()->retake_registered_course_id);
        $this->assertSame(2, RegisteredCourse::where('academic_detail_id', $this->academicDetail->id)->count());
    }

    public function test_ambiguous_or_unavailable_course_is_sent_for_department_review(): void
    {
        $registration = RegisteredCourse::create([
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2023-2024',
        ]);
        $result = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $registration->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2023-2024',
            'total_score' => 35,
            'grade' => 'F',
            'credit_units' => 3,
            'status' => 'released',
        ]);
        $carryOver = $this->carryOverService->recordFailedCourse($result);

        $this->deptCourse->department_id = $this->department->id + 10000;
        $this->deptCourse->save();
        \App\Models\Setting::setValue('ACADEMIC_SESSION', '2024/2025');
        $this->carryOverService->registerEligibleRetakes($this->student, $this->academicDetail);

        $this->assertSame('review_required', $carryOver->fresh()->registration_status);
        $this->assertNull($carryOver->fresh()->retake_registered_course_id);
        $this->assertSame(1, RegisteredCourse::where('academic_detail_id', $this->academicDetail->id)->count());
    }

    public function test_retake_that_exceeds_configured_load_is_held_for_department_review(): void
    {
        $failedRegistration = RegisteredCourse::create([
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2023-2024',
        ]);
        $failedResult = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $failedRegistration->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2023-2024',
            'total_score' => 35,
            'grade' => 'F',
            'credit_units' => 3,
            'status' => 'released',
        ]);
        $carryOver = $this->carryOverService->recordFailedCourse($failedResult);

        $this->deptCourse->units = 5;
        $this->deptCourse->save();

        DepartmentMaxUnit::updateOrCreate(
            ['department_id' => $this->department->id, 'student_level_id' => $this->level->id],
            ['max_units' => 24]
        );
        $regularCourse = StudentCourse::create([
            'code' => 'CSC999',
            'title' => 'Regular course',
            'units' => 20,
            'student_level_id' => $this->level->id,
            'semester' => 2,
        ]);
        $regularOffering = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $regularCourse->id,
            'units' => 20,
        ]);
        RegisteredCourse::create([
            'department_course_id' => $regularOffering->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '20',
            'academic_session' => '2024/2025',
        ]);

        \App\Models\Setting::setValue('ACADEMIC_SESSION', '2024/2025');
        $this->carryOverService->registerEligibleRetakes($this->student, $this->academicDetail);

        $this->assertSame('limit_blocked', $carryOver->fresh()->registration_status);
        $this->assertStringContainsString('limit', strtolower($carryOver->fresh()->review_reason));
        $this->assertSame(2, RegisteredCourse::where('academic_detail_id', $this->academicDetail->id)->count());
    }

    public function test_hod_approved_successor_is_registered_with_its_own_snapshots(): void
    {
        $failedRegistration = RegisteredCourse::create([
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2023-2024',
            'course_code_snapshot' => 'OLD101',
            'course_title_snapshot' => 'Old course',
            'credit_units_snapshot' => 3,
        ]);
        $failedResult = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $failedRegistration->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2023-2024',
            'total_score' => 35,
            'grade' => 'F',
            'credit_units' => 3,
            'status' => 'released',
        ]);
        $carryOver = $this->carryOverService->recordFailedCourse($failedResult);
        $carryOver->forceFill([
            'registration_status' => 'review_required',
            'review_reason' => 'The original offering is unavailable.',
        ])->save();

        $successorCourse = StudentCourse::create([
            'code' => 'CSC202',
            'title' => 'Approved successor',
            'units' => 4,
            'student_level_id' => $this->level->id,
            'semester' => 2,
        ]);
        $successor = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $successorCourse->id,
            'units' => 4,
        ]);
        $reviewer = new User();
        $reviewer->id = (string) \Illuminate\Support\Str::uuid();
        $reviewer->programme_id = ProgrammesEnum::Undergraduate->value;
        $reviewer->email = 'hod' . rand(1000, 9999) . '@test.com';
        $reviewer->password = bcrypt('password');
        $reviewer->vpassword = 'password';
        $reviewer->role = 'hod';
        $reviewer->save();
        HodUser::create(['user_id' => $reviewer->id, 'department_id' => $this->department->id]);

        \App\Models\Setting::setValue('HOD_ACADEMIC_SESSION', '2024/2025');
        \App\Models\Setting::setValue('ACADEMIC_SESSION', '2024/2025');
        $this->carryOverService->approveDepartmentSuccessor(
            $carryOver,
            $successor,
            $reviewer,
            'Department approved the mapped successor after course review.'
        );
        $this->carryOverService->registerEligibleRetakes($this->student, $this->academicDetail);

        $retake = RegisteredCourse::where('academic_detail_id', $this->academicDetail->id)
            ->where('academic_session', '2024/2025')
            ->sole();
        $this->assertSame($successor->id, $retake->department_course_id);
        $this->assertSame('CSC202', $retake->course_code_snapshot);
        $this->assertSame('Approved successor', $retake->course_title_snapshot);
        $this->assertSame(4, (int) $retake->credit_units_snapshot);
        $this->assertSame($retake->id, $carryOver->fresh()->retake_registered_course_id);
    }

    public function test_validates_semester_credit_units(): void
    {
        // Seed a max unit record: 24 units for this department & level
        \App\Models\DepartmentMaxUnit::updateOrCreate(
            [
                'department_id' => $this->department->id,
                'student_level_id' => $this->level->id,
            ],
            ['max_units' => 24]
        );

        // Valid: 15 regular + 3 carry-over = 18 units (within 15–24)
        $valid = $this->carryOverService->validateCreditUnits(15, 3, $this->department->id, $this->level->id);
        $this->assertTrue($valid['is_valid']);
        $this->assertEquals(18, $valid['total_units']);
        $this->assertEquals(24, $valid['max_units']);

        // Over maximum: 22 + 4 = 26 units > 24
        $overMax = $this->carryOverService->validateCreditUnits(22, 4, $this->department->id, $this->level->id);
        $this->assertFalse($overMax['is_valid']);
        $this->assertStringContainsString('exceeds the maximum', $overMax['errors'][0]);

        // Under minimum: 10 + 2 = 12 units < 15
        $underMin = $this->carryOverService->validateCreditUnits(10, 2, $this->department->id, $this->level->id);
        $this->assertFalse($underMin['is_valid']);
        $this->assertStringContainsString('below the minimum', $underMin['errors'][0]);
    }

    public function test_validates_credit_units_falls_back_to_default_when_no_max_unit_record(): void
    {
        // Use a department/level combo that has no department_max_units row
        $fakeDeptId = 99999;
        $fakeLevelId = 99999;

        // Falls back to DEFAULT_MAX_SEMESTER_UNITS (24); 20 + 3 = 23 should be valid
        $result = $this->carryOverService->validateCreditUnits(20, 3, $fakeDeptId, $fakeLevelId);
        $this->assertTrue($result['is_valid']);
        $this->assertEquals(23, $result['total_units']);
        $this->assertEquals(24, $result['max_units']); // Fallback default

        // Over fallback max: 22 + 5 = 27 > 24
        $overFallback = $this->carryOverService->validateCreditUnits(22, 5, $fakeDeptId, $fakeLevelId);
        $this->assertFalse($overFallback['is_valid']);
    }

    public function test_sync_student_clearances_automatically_clears_active_carry_over_when_passed_result_exists(): void
    {
        $originalRegistration = RegisteredCourse::create([
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2023-2024',
        ]);
        $failedResult = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $originalRegistration->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2023-2024',
            'total_score' => 25,
            'grade' => 'F',
            'credit_units' => 3,
            'status' => 'released',
        ]);
        $carryOver = $this->carryOverService->recordFailedCourse($failedResult);
        $this->assertFalse($carryOver->is_cleared);

        $retakeRegistration = RegisteredCourse::create([
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2024-2025',
        ]);
        $carryOver->update(['retake_registered_course_id' => $retakeRegistration->id]);

        $passedResult = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $retakeRegistration->id,
            'department_course_id' => $this->deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'total_score' => 65,
            'grade' => 'B',
            'credit_units' => 3,
            'status' => 'released',
        ]);

        $activeCarryOvers = $this->carryOverService->getActiveCarryOvers($this->student);
        $this->assertCount(0, $activeCarryOvers);
        $this->assertTrue($carryOver->fresh()->is_cleared);
        $this->assertSame('cleared', $carryOver->fresh()->registration_status);
    }
}
