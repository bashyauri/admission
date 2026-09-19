<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\ResultReportingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class CohortProgressionBroadsheetTest extends TestCase
{
    use DatabaseTransactions;

    protected User $examOfficer;
    protected User $coordinator;
    protected User $student1;
    protected User $student2;
    protected Department $department;
    protected Course $course;
    protected string $admissionSession = '2021/2022';

    protected function setUp(): void
    {
        parent::setUp();

        $programme = Programme::create([
            'name' => 'B.Sc Computer Science ' . uniqid(),
            'abv' => 'UG',
        ]);

        $this->department = Department::create([
            'name' => 'Computer Science Dept ' . uniqid(),
            'faculty' => 'Faculty of Science',
        ]);

        $this->course = Course::create([
            'name' => 'B.Sc Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $programme->id,
        ]);

        $level100 = StudentLevel::where('level', '100')->first() ?? StudentLevel::create(['level' => '100']);
        $level400 = StudentLevel::where('level', '400')->first() ?? StudentLevel::create(['level' => '400']);

        // Exam Officer
        $this->examOfficer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'email' => 'examofficer_' . uniqid() . '@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'firstname' => 'Officer',
            'surname' => 'Exam',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        // Coordinator
        $this->coordinator = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'email' => 'coordinator_' . uniqid() . '@example.com',
            'role' => 'coordinator',
            'password' => bcrypt('password'),
            'firstname' => 'Level',
            'surname' => 'Coordinator',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        // Student 1: Incurred a carry-over in 100L and CLEARED it in 200L
        $this->student1 = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'email' => 'student1_' . uniqid() . '@example.com',
            'role' => 'student',
            'password' => bcrypt('password'),
            'firstname' => 'Mansur',
            'surname' => 'Mukhtar',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $acad1 = AcademicDetail::create([
            'user_id' => $this->student1->id,
            'department_id' => $this->department->id,
            'programme_id' => $programme->id,
            'course_id' => $this->course->id,
            'student_level_id' => $level400->id,
            'matric_no' => 'UG21/COMSC/1001',
            'admission_session' => $this->admissionSession,
            'acad_session' => '2024/2025',
        ]);

        // Student 2: Has an OUTSTANDING carry-over
        $this->student2 = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'email' => 'student2_' . uniqid() . '@example.com',
            'role' => 'student',
            'password' => bcrypt('password'),
            'firstname' => 'Zainab',
            'surname' => 'Aliyu',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $acad2 = AcademicDetail::create([
            'user_id' => $this->student2->id,
            'department_id' => $this->department->id,
            'programme_id' => $programme->id,
            'course_id' => $this->course->id,
            'student_level_id' => $level400->id,
            'matric_no' => 'UG21/COMSC/1002',
            'admission_session' => $this->admissionSession,
            'acad_session' => '2024/2025',
        ]);

        // Setup Course Models
        $studentCourse1 = StudentCourse::create([
            'code' => 'CSC101',
            'name' => 'Introduction to Computing',
            'units' => 3,
        ]);

        $deptCourse1 = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'course_id' => $studentCourse1->id,
            'student_level_id' => $level100->id,
            'semester' => 'first',
            'units' => 3,
        ]);

        $regCourse1 = RegisteredCourse::create([
            'user_id' => $this->student1->id,
            'department_course_id' => $deptCourse1->id,
            'academic_session' => '2021/2022',
            'semester' => 'first',
            'student_level_id' => $level100->id,
            'credit_units_snapshot' => 3,
            'course_code_snapshot' => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computing',
        ]);

        // 1. Initial Failed Result for Student 1 in 2021/2022
        $failedRes1 = Result::create([
            'user_id' => $this->student1->id,
            'registered_course_id' => $regCourse1->id,
            'department_course_id' => $deptCourse1->id,
            'academic_detail_id' => $acad1->id,
            'semester' => 'first',
            'academic_session' => '2021/2022',
            'ca_score' => 12,
            'exam_score' => 20,
            'total_score' => 32,
            'grade' => 'F',
            'grade_point' => 0,
            'credit_units' => 3,
            'status' => 'released',
        ]);

        // 2. Retake Passing Result for Student 1 in 2022/2023
        $passedRetakeRes1 = Result::create([
            'user_id' => $this->student1->id,
            'registered_course_id' => $regCourse1->id,
            'department_course_id' => $deptCourse1->id,
            'academic_detail_id' => $acad1->id,
            'semester' => 'first',
            'academic_session' => '2022/2023',
            'ca_score' => 25,
            'exam_score' => 40,
            'total_score' => 65,
            'grade' => 'B',
            'grade_point' => 4,
            'credit_units' => 3,
            'is_repeated' => true,
            'status' => 'released',
        ]);

        // Record Cleared Carry-Over for Student 1
        CarryOverCourse::create([
            'user_id' => $this->student1->id,
            'registered_course_id' => $regCourse1->id,
            'department_course_id' => $deptCourse1->id,
            'failed_session' => '2021/2022',
            'failed_semester' => 'first',
            'failed_score' => 32,
            'failed_grade' => 'F',
            'retake_session' => '2022/2023',
            'retake_semester' => 'first',
            'is_cleared' => true,
            'cleared_at' => now(),
            'cleared_result_id' => $passedRetakeRes1->id,
        ]);

        // Record Outstanding Carry-Over for Student 2
        $regCourse2 = RegisteredCourse::create([
            'user_id' => $this->student2->id,
            'department_course_id' => $deptCourse1->id,
            'academic_session' => '2021/2022',
            'semester' => 'first',
            'student_level_id' => $level100->id,
            'credit_units_snapshot' => 3,
            'course_code_snapshot' => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computing',
        ]);

        Result::create([
            'user_id' => $this->student2->id,
            'registered_course_id' => $regCourse2->id,
            'department_course_id' => $deptCourse1->id,
            'academic_detail_id' => $acad2->id,
            'semester' => 'first',
            'academic_session' => '2021/2022',
            'ca_score' => 10,
            'exam_score' => 15,
            'total_score' => 25,
            'grade' => 'F',
            'grade_point' => 0,
            'credit_units' => 3,
            'status' => 'released',
        ]);

        CarryOverCourse::create([
            'user_id' => $this->student2->id,
            'registered_course_id' => $regCourse2->id,
            'department_course_id' => $deptCourse1->id,
            'failed_session' => '2021/2022',
            'failed_semester' => 'first',
            'failed_score' => 25,
            'failed_grade' => 'F',
            'is_cleared' => false,
        ]);
    }

    public function test_service_generates_accurate_cohort_progression_broadsheet_data(): void
    {
        /** @var ResultReportingService $service */
        $service = app(ResultReportingService::class);

        $data = $service->getCohortProgressionBroadsheet([
            'department_id' => $this->department->id,
            'admission_session' => $this->admissionSession,
        ]);

        $this->assertArrayHasKey('department', $data);
        $this->assertArrayHasKey('students', $data);
        $this->assertArrayHasKey('statistics', $data);

        $this->assertEquals($this->department->id, $data['department']['id']);
        $this->assertCount(2, $data['students']);

        // Find Student 1
        $student1Data = collect($data['students'])->firstWhere('user_id', $this->student1->id);
        $this->assertNotNull($student1Data);
        $this->assertEquals('UG21/COMSC/1001', $student1Data['matric_no']);
        $this->assertFalse($student1Data['has_outstanding_carryovers']);
        $this->assertStringContainsString('ALL CARRY-OVERS CLEARED', $student1Data['standing_remark']);

        // Check Carry-Over Ledger for Student 1
        $this->assertCount(1, $student1Data['carry_overs']);
        $co1 = $student1Data['carry_overs'][0];
        $this->assertEquals('CSC101', $co1['course_code']);
        $this->assertEquals('2021/2022', $co1['failed_session']);
        $this->assertEquals('2022/2023', $co1['retake_session']);
        $this->assertTrue($co1['is_cleared']);
        $this->assertEquals('CLEARED', $co1['status']);
        $this->assertEquals(65.0, $co1['cleared_score']);

        // Find Student 2
        $student2Data = collect($data['students'])->firstWhere('user_id', $this->student2->id);
        $this->assertNotNull($student2Data);
        $this->assertEquals('UG21/COMSC/1002', $student2Data['matric_no']);
        $this->assertTrue($student2Data['has_outstanding_carryovers']);
        $this->assertStringContainsString('DEFICIENT', $student2Data['standing_remark']);

        // Check Carry-Over Ledger for Student 2
        $this->assertCount(1, $student2Data['carry_overs']);
        $co2 = $student2Data['carry_overs'][0];
        $this->assertFalse($co2['is_cleared']);
        $this->assertEquals('OUTSTANDING', $co2['status']);

        // Verify summary statistics
        $stats = $data['statistics'];
        $this->assertEquals(2, $stats['total_students']);
        $this->assertEquals(1, $stats['resolved_carryover_count']);
        $this->assertEquals(1, $stats['deficient_count']);
        $this->assertEquals(1, $stats['total_carryovers_cleared']);
        $this->assertEquals(1, $stats['total_carryovers_outstanding']);
    }

    public function test_cohort_progression_broadsheet_print_view_renders_successfully(): void
    {
        $response = $this->actingAs($this->examOfficer)
            ->get(route('exam-officer.cohort-progression-broadsheet', [
                'department' => $this->department->id,
                'admissionSession' => str_replace('/', '-', $this->admissionSession),
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('reports.cohort-progression-broadsheet');
        $response->assertSee('COHORT PROGRESSION MASTER BROADSHEET');
        $response->assertSee('UG21/COMSC/1001');
        $response->assertSee('UG21/COMSC/1002');
        $response->assertSee('CSC101');
        $response->assertSee('CLEARED');
        $response->assertSee('OUTSTANDING');
    }

    public function test_cohort_progression_broadsheet_csv_export_streams_correctly(): void
    {
        $response = $this->actingAs($this->examOfficer)
            ->get(route('exam-officer.cohort-progression-broadsheet.export', [
                'department' => $this->department->id,
                'admissionSession' => str_replace('/', '-', $this->admissionSession),
            ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Cohort_Progression_Broadsheet', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_coordinator_can_access_cohort_progression_broadsheet(): void
    {
        $response = $this->actingAs($this->coordinator)
            ->get(route('coordinator.cohort-progression-broadsheet', [
                'department' => $this->department->id,
                'admissionSession' => str_replace('/', '-', $this->admissionSession),
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('reports.cohort-progression-broadsheet');
    }
}
