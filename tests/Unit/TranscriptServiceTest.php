<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ProgrammesEnum;
use App\Enums\Role;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\Transcript;
use App\Models\User;
use App\Services\GradeCalculationService;
use App\Services\TranscriptService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranscriptServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $ugStudent;
    protected User $pgStudent;
    protected Department $department;
    protected Programme $ugProgramme;
    protected Programme $pgProgramme;
    protected StudentLevel $level100;
    protected StudentLevel $level200;
    protected AcademicDetail $academicDetail;
    protected Course $course;
    protected TranscriptService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TranscriptService::class);

        $this->department = Department::first() ?? Department::create(['name' => 'Computer Science Department']);

        $this->ugProgramme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate([
                'id'   => ProgrammesEnum::Undergraduate->value,
                'name' => 'Undergraduate',
                'abv'  => 'UG',
            ]);

        $this->pgProgramme = Programme::find(ProgrammesEnum::PG->value)
            ?? Programme::forceCreate([
                'id'   => ProgrammesEnum::PG->value,
                'name' => 'Postgraduate',
                'abv'  => 'PG',
            ]);

        $this->level100 = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);
        $this->level200 = StudentLevel::create(['level' => '200']);

        $this->ugStudent = User::create([
            'email'             => 'ug_trans_' . uniqid() . '@example.com',
            'role'              => Role::STUDENT->value,
            'programme_id'      => $this->ugProgramme->id,
            'surname'           => 'Bello',
            'firstname'         => 'Umar',
            'password'          => bcrypt('password'),
            'vpassword'         => 'password',
            'email_verified_at' => now(),
        ]);

        $this->pgStudent = User::create([
            'email'             => 'pg_trans_' . uniqid() . '@example.com',
            'role'              => Role::STUDENT->value,
            'programme_id'      => $this->pgProgramme->id,
            'surname'           => 'Aliyu',
            'firstname'         => 'Hauwa',
            'password'          => bcrypt('password'),
            'vpassword'         => 'password',
            'email_verified_at' => now(),
        ]);

        $this->course = Course::create([
            'name'          => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id'  => $this->ugProgramme->id,
        ]);

        $this->academicDetail = AcademicDetail::forceCreate([
            'user_id'           => $this->ugStudent->id,
            'matric_no'         => 'UG/2023/CSC/1001',
            'course_id'         => $this->course->id,
            'programme_id'      => $this->ugProgramme->id,
            'department_id'     => $this->department->id,
            'student_level_id'  => $this->level100->id,
            'admission_session' => '2023/2024',
        ]);

        // Configure DepartmentMaxUnit
        DepartmentMaxUnit::updateOrCreate(
            [
                'department_id'    => $this->department->id,
                'student_level_id' => $this->level100->id,
            ],
            [
                'max_units' => 24,
            ]
        );
    }

    public function test_non_undergraduate_student_throws_domain_exception(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('strictly for undergraduate students');

        $this->service->generateTranscript($this->pgStudent);
    }

    public function test_transcript_data_builds_correctly_with_results_and_repeat_tracking(): void
    {
        // Setup: student took CSC101 in 2023/2024 First semester and failed with F
        $scCsc101 = StudentCourse::create([
            'code'             => 'CSC101',
            'title'            => 'Introduction to Computing',
            'units'            => 3,
            'semester'         => 1,
            'student_level_id' => $this->level100->id,
        ]);
        $dcCsc101 = DepartmentCourse::create([
            'student_course_id' => $scCsc101->id,
            'department_id'     => $this->department->id,
            'units'             => 3,
        ]);
        $rc1 = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $dcCsc101->id,
            'student_level_id'     => $this->level100->id,
            'units'                => 3,
            'academic_session'     => '2023/2024',
        ]);
        Result::forceCreate([
            'user_id'               => $this->ugStudent->id,
            'registered_course_id'  => $rc1->id,
            'department_course_id'  => $dcCsc101->id,
            'academic_detail_id'    => $this->academicDetail->id,
            'course_code_snapshot'  => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computing',
            'credit_units_snapshot' => 3,
            'semester'              => 'first',
            'academic_session'      => '2023/2024',
            'ca_score'              => 10,
            'exam_score'            => 25,
            'total_score'           => 35, // Fail (F)
            'grade'                 => 'F',
            'grade_point'           => 0,
            'status'                => 'released',
            'is_repeated'           => false,
        ]);

        // Student repeated CSC101 in 2024/2025 First semester and passed with A
        $scCsc101_2 = StudentCourse::create([
            'code'             => 'CSC101-R',
            'title'            => 'Introduction to Computing (Repeat)',
            'units'            => 3,
            'semester'         => 1,
            'student_level_id' => $this->level200->id,
        ]);
        $dcCsc101_2 = DepartmentCourse::create([
            'student_course_id' => $scCsc101_2->id,
            'department_id'     => $this->department->id,
            'units'             => 3,
        ]);
        $rc2 = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $dcCsc101_2->id,
            'student_level_id'     => $this->level200->id,
            'units'                => 3,
            'academic_session'     => '2024/2025',
        ]);
        Result::forceCreate([
            'user_id'               => $this->ugStudent->id,
            'registered_course_id'  => $rc2->id,
            'department_course_id'  => $dcCsc101_2->id,
            'academic_detail_id'    => $this->academicDetail->id,
            'course_code_snapshot'  => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computing',
            'credit_units_snapshot' => 3,
            'semester'              => 'first',
            'academic_session'      => '2024/2025',
            'ca_score'              => 32,
            'exam_score'            => 48,
            'total_score'           => 80, // Excellent (A)
            'grade'                 => 'A',
            'grade_point'           => 5,
            'status'                => 'released',
            'is_repeated'           => true,
        ]);

        $data = $this->service->buildTranscriptData($this->ugStudent);

        $this->assertCount(2, $data['resultsBreakdown']);

        // First attempt (2023/2024)
        $firstSem = $data['resultsBreakdown'][0];
        $this->assertEquals('2023/2024', $firstSem['academic_session']);
        $this->assertEquals('first', $firstSem['semester']);
        $this->assertFalse($firstSem['courses'][0]['is_repeated']);
        $this->assertEquals(1, $firstSem['courses'][0]['attempt_number']);
        $this->assertEquals('F', $firstSem['courses'][0]['grade']);
        $this->assertEquals(0.00, $firstSem['gpa']);

        // Second attempt (2024/2025) - marked as repeated attempt
        $secondSem = $data['resultsBreakdown'][1];
        $this->assertEquals('2024/2025', $secondSem['academic_session']);
        $this->assertTrue($secondSem['courses'][0]['is_repeated']);
        $this->assertEquals(2, $secondSem['courses'][0]['attempt_number']);
        $this->assertEquals('A', $secondSem['courses'][0]['grade']);
        $this->assertEquals(5.00, $secondSem['gpa']);

        // Check running cumulative stats:
        // Total CCR: 3 + 3 = 6
        // Total CQP: (0*3) + (5*3) = 15
        // CGPA: 15 / 6 = 2.50
        $this->assertEquals(6, $secondSem['ccr']);
        $this->assertEquals(15, $secondSem['cqp']);
        $this->assertEquals(2.50, $secondSem['cgpa']);

        // DepartmentMaxUnit verification
        $this->assertEquals(24, $data['departmentMaxUnit']);

        // Transcript record & QR Code created
        $this->assertInstanceOf(Transcript::class, $data['transcript']);
        $this->assertNotEmpty($data['transcript']->verification_code);
        $this->assertNotEmpty($data['qrCodeSvg']);
    }

    public function test_grade_calculation_service_cgpa_method(): void
    {
        $gradeService = app(GradeCalculationService::class);

        // CSC101 (3 units, Grade A = 15 QP)
        $sc1 = StudentCourse::create([
            'code'             => 'CSC101',
            'title'            => 'Intro to CS',
            'units'            => 3,
            'semester'         => 1,
            'student_level_id' => $this->level100->id,
        ]);
        $dc1 = DepartmentCourse::create([
            'student_course_id' => $sc1->id,
            'department_id'     => $this->department->id,
            'units'             => 3,
        ]);
        $rc1 = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $dc1->id,
            'student_level_id'     => $this->level100->id,
            'units'                => 3,
            'academic_session'     => '2023/2024',
        ]);
        Result::forceCreate([
            'user_id'               => $this->ugStudent->id,
            'registered_course_id'  => $rc1->id,
            'department_course_id'  => $dc1->id,
            'academic_detail_id'    => $this->academicDetail->id,
            'course_code_snapshot'  => 'CSC101',
            'course_title_snapshot' => 'Intro to CS',
            'credit_units_snapshot' => 3,
            'semester'              => 'first',
            'academic_session'      => '2023/2024',
            'ca_score'              => 35,
            'exam_score'            => 45,
            'total_score'           => 80,
            'grade'                 => 'A',
            'grade_point'           => 5,
            'status'                => 'released',
        ]);

        // MTH101 (2 units, Grade B = 8 QP)
        $sc2 = StudentCourse::create([
            'code'             => 'MTH101',
            'title'            => 'Mathematics',
            'units'            => 2,
            'semester'         => 1,
            'student_level_id' => $this->level100->id,
        ]);
        $dc2 = DepartmentCourse::create([
            'student_course_id' => $sc2->id,
            'department_id'     => $this->department->id,
            'units'             => 2,
        ]);
        $rc2 = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $dc2->id,
            'student_level_id'     => $this->level100->id,
            'units'                => 2,
            'academic_session'     => '2023/2024',
        ]);
        Result::forceCreate([
            'user_id'               => $this->ugStudent->id,
            'registered_course_id'  => $rc2->id,
            'department_course_id'  => $dc2->id,
            'academic_detail_id'    => $this->academicDetail->id,
            'course_code_snapshot'  => 'MTH101',
            'course_title_snapshot' => 'Mathematics',
            'credit_units_snapshot' => 2,
            'semester'              => 'first',
            'academic_session'      => '2023/2024',
            'ca_score'              => 28,
            'exam_score'            => 38,
            'total_score'           => 66,
            'grade'                 => 'B',
            'grade_point'           => 4,
            'status'                => 'released',
        ]);

        $cgpaData = $gradeService->calculateCGPA($this->ugStudent->id);

        // Total units = 5, Total points = 15 + 8 = 23. CGPA = 23/5 = 4.60
        $this->assertEquals(5, $cgpaData['total_credit_units']);
        $this->assertEquals(23, $cgpaData['total_grade_points']);
        $this->assertEquals(4.60, $cgpaData['cgpa']);
        $this->assertEquals('First Class Honours', $cgpaData['class_of_degree']);
    }

    public function test_pdf_generation_completes_successfully(): void
    {
        $pdf = $this->service->generateTranscript($this->ugStudent, true);
        $this->assertInstanceOf(\Barryvdh\DomPDF\PDF::class, $pdf);

        $output = $pdf->output();
        $this->assertNotEmpty($output);
        // PDF magic bytes: %PDF-
        $this->assertStringStartsWith('%PDF-', $output);
    }
}
