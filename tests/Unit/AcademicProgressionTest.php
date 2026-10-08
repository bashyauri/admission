<?php

namespace Tests\Unit;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\AcademicProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicProgressionTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicProgressionService $progressionService;
    protected Department $department;
    protected Programme $programme;
    protected StudentLevel $level100;
    protected StudentLevel $level200;
    protected StudentLevel $level400;

    protected function setUp(): void
    {
        parent::setUp();
        $this->progressionService = new AcademicProgressionService();

        $this->department = Department::first() ?? new Department();
        if (!$this->department->exists) {
            $this->department->name = 'CS Dept ' . rand(100, 999);
            $this->department->save();
        }

        $this->programme = Programme::first() ?? new Programme();
        if (!$this->programme->exists) {
            $this->programme->name = 'B.Sc CS';
            $this->programme->abv = 'CS';
            $this->programme->save();
        }

        $this->level100 = StudentLevel::where('level', '100')->first() ?? StudentLevel::create(['level' => '100']);
        $this->level200 = StudentLevel::where('level', '200')->first() ?? StudentLevel::create(['level' => '200']);
        $level300 = StudentLevel::where('level', '300')->first() ?? StudentLevel::create(['level' => '300']);
        $this->level400 = StudentLevel::where('level', '400')->first() ?? StudentLevel::create(['level' => '400']);
    }

    public function test_fresh_utme_student_starts_at_level_1(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'fresh' . rand(1000, 9999) . '@test.com';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        $level = $this->progressionService->getNextEligibleLevel($user);
        $this->assertEquals(1, $level);
    }

    public function test_student_with_good_standing_is_promoted(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'good' . rand(1000, 9999) . '@test.com';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        $course = Course::first() ?? Course::create([
            'name' => 'CS101',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level100->id, // 100L (id 1)
            'acad_session' => '2023-2024',
        ]);

        ResultGpaRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2023-2024',
            'semester' => 'second',
            'semester_gpa' => 3.50,
            'cumulative_gpa' => 3.50,
            'total_credit_units' => 20,
            'total_grade_points' => 70,
            'cumulative_credit_units' => 40,
            'cumulative_grade_points' => 140,
            'class_of_degree' => 'Second Class Upper Division',
        ]);

        $standing = $this->progressionService->determineAcademicStanding($user);
        $this->assertEquals(AcademicProgressionService::STANDING_PROMOTED, $standing['standing']);

        $nextLevel = $this->progressionService->getNextEligibleLevel($user);
        $this->assertEquals(2, $nextLevel); // Promoted to 200L
    }

    public function test_student_with_poor_standing_is_on_probation_under_nigerian_system(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'repeat' . rand(1000, 9999) . '@test.com';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        $course = Course::first() ?? Course::create([
            'name' => 'CS101',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level100->id, // 100L
            'acad_session' => '2023-2024',
        ]);

        ResultGpaRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2023-2024',
            'semester' => 'second',
            'semester_gpa' => 0.85,
            'cumulative_gpa' => 0.85, // Poor standing but managed as probation under the Nigerian-style rule
            'total_credit_units' => 20,
            'total_grade_points' => 17,
            'cumulative_credit_units' => 40,
            'cumulative_grade_points' => 34,
            'class_of_degree' => 'Fail',
        ]);

        $standing = $this->progressionService->determineAcademicStanding($user);
        $this->assertEquals(AcademicProgressionService::STANDING_PROBATION, $standing['standing']);

        $nextLevel = $this->progressionService->getNextEligibleLevel($user);
        $this->assertEquals(2, $nextLevel); // Probation does not force a whole-level repeat
    }

    public function test_final_year_spillover_student_capped_at_max_level(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'spill' . rand(1000, 9999) . '@test.com';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        $course = new Course();
        $course->name = 'CS401';
        $course->department_id = $this->department->id;
        $course->programme_id = $this->programme->id;
        $course->semesters = '8'; // 4-year programme (8 semesters)
        $course->save();

        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level400->id, // 400L in a 4-year degree
            'acad_session' => '2023-2024',
        ]);

        // Student has an active uncleared carry-over
        $studentCourse = StudentCourse::first() ?? StudentCourse::create([
            'code' => 'CSC101' . rand(10, 99),
            'title' => 'Intro Prog ' . rand(10, 99),
            'units' => '3',
            'student_level_id' => $this->level100->id,
            'semester' => 1,
        ]);

        $deptCourse = DepartmentCourse::first() ?? DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);

        $reg = RegisteredCourse::create([
            'department_course_id' => $deptCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'student_level_id' => $this->level400->id,
            'units' => '3',
            'academic_session' => '2023-2024',
        ]);

        CarryOverCourse::create([
            'user_id' => $user->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $deptCourse->id,
            'failed_session' => '2023-2024',
            'failed_semester' => 'first',
            'failed_score' => 30,
            'failed_grade' => 'F',
            'is_cleared' => false,
        ]);

        ResultGpaRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2023-2024',
            'semester' => 'second',
            'semester_gpa' => 2.50,
            'cumulative_gpa' => 2.50,
            'total_credit_units' => 20,
            'total_grade_points' => 50,
            'cumulative_credit_units' => 140,
            'cumulative_grade_points' => 350,
            'class_of_degree' => 'Second Class Lower Division',
        ]);

        $standing = $this->progressionService->determineAcademicStanding($user);
        $this->assertEquals(AcademicProgressionService::STANDING_SPILLOVER, $standing['standing']);

        // Should be capped at 4 (400 Level), never overflowing to 500 Level
        $nextLevel = $this->progressionService->getNextEligibleLevel($user);
        $this->assertEquals(4, $nextLevel);
    }

    public function test_process_and_apply_academic_progression_sets_probation_automatically(): void
    {
        config(['academic_withdrawal.auto_apply' => true, 'academic_withdrawal.bypass_senate' => true]);

        $course = Course::first() ?? Course::create([
            'name' => 'CS101',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->surname = 'Probation';
        $user->firstname = 'Student';
        $user->email = 'probation_' . uniqid() . '@example.com';
        $user->password = bcrypt('secret');
        $user->vpassword = 'secret';
        $user->role = 'student';
        $user->save();

        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/PROB/' . rand(100, 999),
            'course_id' => $course->id,
            'department_id' => $this->department->id,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $this->level100->id,
            'acad_session' => '2023/2024',
            'admission_session' => '2023/2024',
        ]);

        ResultGpaRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2023/2024',
            'semester' => 'second',
            'semester_gpa' => 0.85,
            'cumulative_gpa' => 0.85,
            'total_credit_units' => 20,
            'total_grade_points' => 17,
            'cumulative_credit_units' => 20,
            'cumulative_grade_points' => 17,
            'class_of_degree' => 'Below Degree Standard',
        ]);

        $result = $this->progressionService->processAndApplyAcademicProgression($user, '2023/2024', 2);

        $this->assertEquals(AcademicProgressionService::STANDING_PROBATION, $result['standing']);
        $this->assertFalse($result['withdrawal_applied']);

        $progRecord = \App\Models\AcademicProgressionRecord::where('user_id', $user->id)
            ->where('academic_session', '2023/2024')
            ->first();
        $this->assertNotNull($progRecord);
        $this->assertEquals(AcademicProgressionService::STANDING_PROBATION, $progRecord->standing);
    }

    public function test_two_consecutive_probation_sessions_automatically_applies_withdrawal_and_bypasses_senate(): void
    {
        config(['academic_withdrawal.auto_apply' => true, 'academic_withdrawal.bypass_senate' => true]);

        $course = Course::first() ?? Course::create([
            'name' => 'CS101',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->surname = 'Withdrawal';
        $user->firstname = 'Student';
        $user->email = 'withdraw_' . uniqid() . '@example.com';
        $user->password = bcrypt('secret');
        $user->vpassword = 'secret';
        $user->role = 'student';
        $user->save();

        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/WDR/' . rand(100, 999),
            'course_id' => $course->id,
            'department_id' => $this->department->id,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $this->level100->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2023/2024',
        ]);

        // First session: probation (2023/2024)
        \App\Models\AcademicProgressionRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2023/2024',
            'semester' => 2,
            'level' => '100',
            'cgpa' => 0.85,
            'standing' => AcademicProgressionService::STANDING_PROBATION,
            'withdrawal_recommended' => false,
        ]);

        // Second session GPA: probation again (2024/2025)
        ResultGpaRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2024/2025',
            'semester' => 'second',
            'semester_gpa' => 0.80,
            'cumulative_gpa' => 0.80,
            'total_credit_units' => 20,
            'total_grade_points' => 16,
            'cumulative_credit_units' => 40,
            'cumulative_grade_points' => 32,
            'class_of_degree' => 'Below Degree Standard',
        ]);

        // Process progression for the second session
        $result = $this->progressionService->processAndApplyAcademicProgression($user, '2024/2025', 2);

        $this->assertEquals(AcademicProgressionService::STANDING_PROBATION, $result['standing']);
        $this->assertTrue($result['withdrawal_applied']);
        $this->assertEquals('CONSECUTIVE_PROBATION', $result['reason_code']);

        // Verify StudentStatusRecord was created and approved immediately (bypassing Senate)
        $statusRecord = \App\Models\StudentStatusRecord::where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($statusRecord);
        $this->assertEquals(\App\Enums\StudentStatus::ACADEMIC_WITHDRAWAL_PROGRAM, $statusRecord->status);
        $this->assertEquals(\App\Services\StudentStatusService::WORKFLOW_SENATE_APPROVED, $statusRecord->senate_decision);
        $this->assertNotNull($statusRecord->senate_reference);
        $this->assertNotNull($statusRecord->senate_decision_date);
    }

    public function test_fubk_academic_standing_thresholds_and_consecutive_probation(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'fubk_test_' . uniqid() . '@example.com';
        $user->password = bcrypt('secret');
        $user->vpassword = 'secret';
        $user->role = 'student';
        $user->save();

        $course = Course::first() ?? Course::create([
            'name' => 'CS101',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/FUBK/' . rand(100, 999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level100->id,
            'acad_session' => '2024/2025',
        ]);

        // CGPA 0.00–0.49 -> W/U
        $standing020 = $this->progressionService->determineAcademicStanding($user, 0.20);
        $this->assertEquals(AcademicProgressionService::STANDING_WITHDRAWN_UNIVERSITY, $standing020['standing']);

        // CGPA 0.50–0.74 -> W/P
        $standing065 = $this->progressionService->determineAcademicStanding($user, 0.65);
        $this->assertEquals(AcademicProgressionService::STANDING_WITHDRAWN_PROGRAM, $standing065['standing']);

        // CGPA 0.75–0.99 -> PROBATION
        $standing085 = $this->progressionService->determineAcademicStanding($user, 0.85);
        $this->assertEquals(AcademicProgressionService::STANDING_PROBATION, $standing085['standing']);

        // CGPA >= 1.00 -> Good Standing / PROMOTED
        $standing250 = $this->progressionService->determineAcademicStanding($user, 2.50);
        $this->assertEquals(AcademicProgressionService::STANDING_PROMOTED, $standing250['standing']);
    }

    public function test_withdrawal_eligibility_ignores_unreleased_results_when_gpa_is_unavailable(): void
    {
        config(['academic_withdrawal.minimum_cgpa.enabled' => true]);
        [$user, $academicDetail, $registration, $departmentCourse] = $this->createStudentWithRegistration('pending-gpa');

        Result::create([
            'user_id' => $user->id,
            'registered_course_id' => $registration->id,
            'department_course_id' => $departmentCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2024/2025',
            'semester' => 'first',
            'total_score' => 90,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'exam_officer_approved',
        ]);

        $eligibility = $this->progressionService->evaluateWithdrawalEligibility($user);

        $this->assertFalse($eligibility['eligible']);
        $this->assertNull($eligibility['reason_code']);
        $this->assertNull($eligibility['cgpa']);
        $this->assertStringContainsString('released result GPA data', $eligibility['reason']);
    }

    public function test_withdrawal_eligibility_falls_back_to_released_results_when_gpa_record_is_missing(): void
    {
        config(['academic_withdrawal.minimum_cgpa.enabled' => true]);
        [$user, $academicDetail, $registration, $departmentCourse] = $this->createStudentWithRegistration('released-gpa');

        Result::create([
            'user_id' => $user->id,
            'registered_course_id' => $registration->id,
            'department_course_id' => $departmentCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2024/2025',
            'semester' => 'first',
            'total_score' => 90,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'released',
        ]);

        $eligibility = $this->progressionService->evaluateWithdrawalEligibility($user);

        $this->assertFalse($eligibility['eligible']);
        $this->assertEquals(5.0, $eligibility['cgpa']);
        $this->assertNull($eligibility['reason_code']);
    }

    public function test_released_zero_cgpa_still_triggers_university_minimum_rule(): void
    {
        config(['academic_withdrawal.minimum_cgpa.enabled' => true]);
        [$user, $academicDetail, $registration, $departmentCourse] = $this->createStudentWithRegistration('zero-gpa');

        Result::create([
            'user_id' => $user->id,
            'registered_course_id' => $registration->id,
            'department_course_id' => $departmentCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2024/2025',
            'semester' => 'first',
            'total_score' => 20,
            'grade' => 'F',
            'grade_point' => 0,
            'credit_units' => 3,
            'grade_point_total' => 0,
            'status' => 'released',
        ]);

        $eligibility = $this->progressionService->evaluateWithdrawalEligibility($user);

        $this->assertTrue($eligibility['eligible']);
        $this->assertEquals('CGPA_BELOW_UNIVERSITY_MINIMUM', $eligibility['reason_code']);
        $this->assertEquals(0.0, $eligibility['cgpa']);
    }

    /** @return array{User, AcademicDetail, RegisteredCourse, DepartmentCourse} */
    private function createStudentWithRegistration(string $suffix): array
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = $suffix . '_' . uniqid() . '@example.com';
        $user->password = bcrypt('secret');
        $user->vpassword = 'secret';
        $user->role = 'student';
        $user->save();

        $course = Course::create([
            'name' => 'Course ' . $suffix,
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
            'semesters' => '8',
        ]);
        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/' . strtoupper($suffix) . '/' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level100->id,
            'acad_session' => '2024/2025',
        ]);

        $studentCourse = StudentCourse::create([
            'code' => 'TST' . rand(1000, 9999),
            'title' => 'Test Course ' . $suffix,
            'units' => '3',
            'student_level_id' => $this->level100->id,
            'semester' => 1,
        ]);
        $departmentCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);
        $registration = RegisteredCourse::create([
            'department_course_id' => $departmentCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'student_level_id' => $this->level100->id,
            'units' => '3',
            'academic_session' => '2024/2025',
        ]);

        return [$user, $academicDetail, $registration, $departmentCourse];
    }
}
