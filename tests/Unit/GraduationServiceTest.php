<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\GraduationEligibility;
use App\Models\GraduationList;
use App\Models\GraduationListItem;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\GradeCalculationService;
use App\Services\GraduationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

class GraduationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GraduationService $service;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
    protected StudentLevel $level100;
    protected StudentLevel $level200;
    protected StudentLevel $level300;
    protected StudentLevel $level400;
    protected User $student;
    protected User $officer;
    protected AcademicDetail $academicDetail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(GraduationService::class);

        $this->department = Department::create([
            'name' => 'Computer Science Department',
        ]);

        $this->programme = Programme::create([
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $this->course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $this->level100 = StudentLevel::create(['level' => '100']);
        $this->level200 = StudentLevel::create(['level' => '200']);
        $this->level300 = StudentLevel::create(['level' => '300']);
        $this->level400 = StudentLevel::create(['level' => '400']);

        $this->student = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'student_' . uniqid() . '@fubk.edu.ng',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'role' => 'student',
            'firstname' => 'Musa',
            'surname' => 'Ibrahim',
            'm_name' => 'Bello',
        ]);

        $this->officer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'officer_' . uniqid() . '@fubk.edu.ng',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'role' => 'admin',
            'firstname' => 'Usman',
            'surname' => 'AcademicAffairs',
        ]);

        $this->academicDetail = AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'UG/20/CS/1044',
            'course_id' => $this->course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level400->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2020/2021',
        ]);
    }

    /**
     * Helper to seed standard passing results for a student.
     */
    protected function createPassedResult(
        string $code,
        string $title,
        int $units,
        string $grade = 'A',
        int $point = 5,
        float $score = 75.0,
        string $session = '2024/2025'
    ): Result {
        $studentCourse = StudentCourse::create([
            'code' => $code,
            'title' => $title,
            'units' => $units,
            'student_level_id' => $this->level400->id,
            'semester' => 1,
        ]);

        $deptCourse = DepartmentCourse::create([
            'student_course_id' => $studentCourse->id,
            'department_id' => $this->department->id,
            'units' => $units,
        ]);

        $registeredCourse = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $deptCourse->id,
            'student_level_id'     => $this->level400->id,
            'units'                => $units,
            'academic_session'     => $session,
        ]);

        return Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $registeredCourse->id,
            'department_course_id' => $deptCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'course_code_snapshot' => $code,
            'course_title_snapshot' => $title,
            'credit_units_snapshot' => $units,
            'credit_units' => $units,
            'semester' => '1',
            'academic_session' => $session,
            'ca_score' => 30.0,
            'exam_score' => $score - 30.0,
            'total_score' => $score,
            'grade' => $grade,
            'grade_point' => $point,
            'grade_point_total' => $point * $units,
            'status' => 'released',
        ]);
    }

    public function test_evaluates_fully_eligible_undergraduate_student(): void
    {
        // 1. General Studies (GST)
        $this->createPassedResult('GST101', 'Communication in English I', 2);
        // 2. SIWES
        $this->createPassedResult('SWE300', 'Students Industrial Work Experience Scheme (SIWES)', 6);
        // 3. Entrepreneurship (ENT)
        $this->createPassedResult('ENT201', 'Entrepreneurship and Innovation', 2);
        // 4. Core Undergraduate Courses to reach >= 120 units
        $this->createPassedResult('CSC401', 'Software Engineering II', 30);
        $this->createPassedResult('CSC402', 'Artificial Intelligence', 30);
        $this->createPassedResult('CSC403', 'Computer Networks & Security', 30);
        $this->createPassedResult('CSC499', 'Final Year Research Project', 25);
        // Total units = 2 + 6 + 2 + 30 + 30 + 30 + 25 = 125 units (>= 120 required)

        $result = $this->service->checkEligibility($this->student);

        $this->assertTrue($result['eligible']);
        $this->assertTrue($result['meets_cgpa']);
        $this->assertTrue($result['meets_units']);
        $this->assertTrue($result['general_studies_completed']);
        $this->assertTrue($result['siwes_completed']);
        $this->assertTrue($result['entrepreneurship_completed']);
        $this->assertTrue($result['no_outstanding_courses']);
        $this->assertEquals(125, $result['total_units_earned']);
        $this->assertEquals(120, $result['total_units_required']);
        $this->assertGreaterThanOrEqual(1.00, $result['final_cgpa']);
        $this->assertEmpty($result['deficiencies']);

        $this->assertDatabaseHas('graduation_eligibilities', [
            'user_id' => $this->student->id,
            'academic_session' => '2024/2025',
            'meets_requirements' => 1,
            'general_studies_completed' => 1,
            'siwes_completed' => 1,
            'entrepreneurship_completed' => 1,
            'total_units_earned' => 125,
            'total_units_required' => 120,
        ]);
    }

    public function test_fails_eligibility_when_cgpa_below_threshold(): void
    {
        // Create results resulting in CGPA below 1.00 (all F's / low points)
        // With GradeCalculationService, scores < 40 get grade F and grade_point 0
        $this->createPassedResult('GST101', 'Communication in English', 2, 'E', 1, 40.0);
        $this->createPassedResult('SWE300', 'SIWES', 6, 'E', 1, 40.0);
        $this->createPassedResult('ENT201', 'Entrepreneurship', 2, 'E', 1, 40.0);
        $this->createPassedResult('CSC401', 'Advanced Computing', 115, 'E', 1, 40.0);

        // All E's is CGPA 1.00. Now test with custom min_cgpa threshold of 1.50 (Third Class minimum)
        $result = $this->service->checkEligibility($this->student, '2024/2025', ['min_cgpa' => 1.50]);

        $this->assertFalse($result['eligible']);
        $this->assertFalse($result['meets_cgpa']);
        $this->assertNotEmpty($result['deficiencies']);
        $this->assertStringContainsString('Final CGPA', $result['deficiencies'][0]);
    }

    public function test_fails_eligibility_when_units_earned_below_required(): void
    {
        // Only 50 units earned out of 120 required
        $this->createPassedResult('GST101', 'Communication in English', 2);
        $this->createPassedResult('SWE300', 'SIWES Industrial Training', 6);
        $this->createPassedResult('ENT201', 'Entrepreneurship', 2);
        $this->createPassedResult('CSC401', 'Software Engineering', 40);

        $result = $this->service->checkEligibility($this->student);

        $this->assertFalse($result['eligible']);
        $this->assertFalse($result['meets_units']);
        $this->assertEquals(50, $result['total_units_earned']);
        $this->assertEquals(120, $result['total_units_required']);
        $this->assertContains('Total units earned (50) is below the required 120 units.', $result['deficiencies']);
    }

    public function test_respects_department_max_units_table_constraints(): void
    {
        // Populate department_max_units table for this department
        DepartmentMaxUnit::create([
            'department_id' => $this->department->id,
            'student_level_id' => $this->level100->id,
            'max_units' => 24,
        ]);
        DepartmentMaxUnit::create([
            'department_id' => $this->department->id,
            'student_level_id' => $this->level200->id,
            'max_units' => 24,
        ]);
        DepartmentMaxUnit::create([
            'department_id' => $this->department->id,
            'student_level_id' => $this->level300->id,
            'max_units' => 24,
        ]);
        DepartmentMaxUnit::create([
            'department_id' => $this->department->id,
            'student_level_id' => $this->level400->id,
            'max_units' => 24,
        ]);

        // 1. Verify getDepartmentMaxUnits returns configured value from department_max_units
        $level400Max = $this->service->getDepartmentMaxUnits($this->department->id, $this->level400->id);
        $this->assertEquals(24, $level400Max);

        // 2. Verify getDepartmentTotalMaxUnits sums ceilings across levels (24 * 4 = 96)
        $totalDeptMax = $this->service->getDepartmentTotalMaxUnits($this->department->id);
        $this->assertEquals(96, $totalDeptMax);

        // 3. Verify getRequiredUnits with use_department_max returns the department total ceiling
        $requiredUnits = $this->service->getRequiredUnits($this->student, $this->academicDetail, ['use_department_max' => true]);
        $this->assertEquals(96, $requiredUnits);

        // 4. Default fallback returns NUC standard 120 units
        $defaultUnits = $this->service->getRequiredUnits($this->student, $this->academicDetail);
        $this->assertEquals(120, $defaultUnits);
    }

    public function test_fails_eligibility_when_compulsory_courses_missing(): void
    {
        // Student earns 130 units but missed SIWES and ENT
        $this->createPassedResult('GST101', 'General Studies English', 10);
        $this->createPassedResult('CSC401', 'General Computer Science', 120);

        $result = $this->service->checkEligibility($this->student);

        $this->assertFalse($result['eligible']);
        $this->assertTrue($result['general_studies_completed']);
        $this->assertFalse($result['siwes_completed']);
        $this->assertFalse($result['entrepreneurship_completed']);

        $this->assertContains('Mandatory SIWES / Industrial Training clearance has not been satisfied.', $result['deficiencies']);
        $this->assertContains('Mandatory Entrepreneurship (ENT) courses have not been completed or passed.', $result['deficiencies']);
    }

    public function test_fails_eligibility_when_active_carry_over_courses_exist(): void
    {
        // Meets units and compulsory courses
        $this->createPassedResult('GST101', 'GST English', 2);
        $this->createPassedResult('SWE300', 'SIWES Training', 6);
        $this->createPassedResult('ENT201', 'Entrepreneurship Studies', 2);
        $this->createPassedResult('CSC401', 'Core Course', 115);

        // Create an active, uncleared carry-over record
        $failedSc = StudentCourse::create([
            'code' => 'MTH101',
            'title' => 'Elementary Mathematics I',
            'units' => 3,
            'student_level_id' => $this->level100->id,
            'semester' => 1,
        ]);
        $failedDc = DepartmentCourse::create([
            'student_course_id' => $failedSc->id,
            'department_id' => $this->department->id,
            'units' => 3,
        ]);

        $failedRc = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $failedDc->id,
            'student_level_id'     => $this->level100->id,
            'units'                => 3,
            'academic_session'     => '2023/2024',
        ]);

        CarryOverCourse::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $failedRc->id,
            'department_course_id' => $failedDc->id,
            'failed_session' => '2023/2024',
            'failed_semester' => '1',
            'failed_score' => 28.0,
            'failed_grade' => 'F',
            'is_cleared' => false,
        ]);

        $result = $this->service->checkEligibility($this->student);

        $this->assertFalse($result['eligible']);
        $this->assertFalse($result['no_outstanding_courses']);
        $this->assertContains('Student has outstanding uncleared carry-over or failed courses.', $result['deficiencies']);
    }

    public function test_clear_student_records_officer_and_timestamp(): void
    {
        $eligibility = GraduationEligibility::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'final_cgpa' => 4.50,
            'class_of_degree' => 'First Class Honours',
            'total_units_earned' => 130,
            'total_units_required' => 120,
            'meets_requirements' => true,
            'siwes_completed' => true,
            'general_studies_completed' => true,
            'entrepreneurship_completed' => true,
            'is_cleared' => false,
        ]);

        $cleared = $this->service->clearStudent($eligibility, $this->officer, 'Approved by Senate Committee');

        $this->assertTrue($cleared->is_cleared);
        $this->assertEquals($this->officer->id, $cleared->cleared_by);
        $this->assertNotNull($cleared->cleared_at);
        $this->assertEquals('Approved by Senate Committee', $cleared->remarks);

        $this->assertDatabaseHas('graduation_eligibilities', [
            'id' => $eligibility->id,
            'is_cleared' => 1,
            'cleared_by' => $this->officer->id,
        ]);
    }

    public function test_clear_student_throws_exception_when_requirements_not_met(): void
    {
        $eligibility = GraduationEligibility::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'final_cgpa' => 0.85,
            'meets_requirements' => false,
            'is_cleared' => false,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->clearStudent($eligibility, $this->officer);
    }

    public function test_add_to_graduation_list_stages_cleared_graduand(): void
    {
        $eligibility = GraduationEligibility::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'final_cgpa' => 4.60,
            'class_of_degree' => 'First Class Honours',
            'total_units_earned' => 135,
            'total_units_required' => 120,
            'meets_requirements' => true,
            'is_cleared' => true,
            'cleared_by' => $this->officer->id,
            'cleared_at' => now(),
        ]);

        $item = $this->service->addToGraduationList($eligibility, '2024/2025');

        $this->assertInstanceOf(GraduationListItem::class, $item);
        $this->assertEquals($this->student->id, $item->user_id);
        $this->assertEquals($this->academicDetail->matric_no, $item->matric_no);
        $this->assertEquals(4.60, $item->final_cgpa);
        $this->assertEquals('First Class Honours', $item->class_of_degree);

        $this->assertDatabaseHas('graduation_list_items', [
            'user_id' => $this->student->id,
            'matric_no' => 'UG/20/CS/1044',
            'final_cgpa' => 4.60,
        ]);
    }

    public function test_audit_cohort_evaluates_all_departmental_candidates(): void
    {
        // Second student in same department
        $student2 = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'student2_' . uniqid() . '@fubk.edu.ng',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'role' => 'student',
            'firstname' => 'Fatima',
            'surname' => 'Aliyu',
        ]);

        AcademicDetail::create([
            'user_id' => $student2->id,
            'matric_no' => 'UG/20/CS/1088',
            'course_id' => $this->course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level400->id,
            'acad_session' => '2024/2025',
        ]);

        $cohortAudit = $this->service->auditCohort($this->department->id, '2024/2025', $this->level400->id);

        $this->assertCount(2, $cohortAudit);
        $this->assertEquals(2, GraduationEligibility::where('academic_session', '2024/2025')->count());
    }
}
