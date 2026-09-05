<?php

namespace Tests\Unit;

use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\GradeCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected GradeCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GradeCalculationService();
    }

    public function test_nuc_5_point_grading_scale_lookup(): void
    {
        // A: 70 - 100 (5 points)
        $aGrade = $this->service->getGradeAndPoint(85.5);
        $this->assertEquals('A', $aGrade['grade']);
        $this->assertEquals(5, $aGrade['grade_point']);
        $this->assertEquals('A', $this->service->calculateGrade(85.5));
        $this->assertEquals(5, $this->service->calculateGradePoint('A'));
        $this->assertEquals(5, $this->service->calculateGradePoint(85.5));

        // B: 60 - 69 (4 points)
        $bGrade = $this->service->getGradeAndPoint(63.0);
        $this->assertEquals('B', $bGrade['grade']);
        $this->assertEquals(4, $bGrade['grade_point']);
        $this->assertEquals('B', $this->service->calculateGrade(63.0));
        $this->assertEquals(4, $this->service->calculateGradePoint('B'));

        // C: 50 - 59 (3 points)
        $cGrade = $this->service->getGradeAndPoint(54.0);
        $this->assertEquals('C', $cGrade['grade']);
        $this->assertEquals(3, $cGrade['grade_point']);
        $this->assertEquals('C', $this->service->calculateGrade(54.0));
        $this->assertEquals(3, $this->service->calculateGradePoint('C'));

        // D: 45 - 49 (2 points)
        $dGrade = $this->service->getGradeAndPoint(47.0);
        $this->assertEquals('D', $dGrade['grade']);
        $this->assertEquals(2, $dGrade['grade_point']);
        $this->assertEquals('D', $this->service->calculateGrade(47.0));
        $this->assertEquals(2, $this->service->calculateGradePoint('D'));

        // F: 0 - 44 (0 points)
        $fGrade = $this->service->getGradeAndPoint(39.5);
        $this->assertEquals('F', $fGrade['grade']);
        $this->assertEquals(0, $fGrade['grade_point']);
        $this->assertEquals('F', $this->service->calculateGrade(39.5));
        $this->assertEquals(0, $this->service->calculateGradePoint('F'));
    }

    public function test_nuc_class_of_degree_classification(): void
    {
        $this->assertEquals('First Class Honours', $this->service->getClassOfDegree(4.75));
        $this->assertEquals('Second Class Upper Division', $this->service->getClassOfDegree(3.80));
        $this->assertEquals('Second Class Lower Division', $this->service->getClassOfDegree(2.75));
        $this->assertEquals('Third Class Honours', $this->service->getClassOfDegree(1.85));
        $this->assertEquals('Pass', $this->service->getClassOfDegree(1.20));
        $this->assertEquals('Fail', $this->service->getClassOfDegree(0.85));
    }

    public function test_quality_points_calculation(): void
    {
        // 3-unit course with Grade A (5 points) => 15 quality points
        $this->assertEquals(15, $this->service->calculateQualityPoints(5, 3));

        // 2-unit course with Grade C (3 points) => 6 quality points
        $this->assertEquals(6, $this->service->calculateQualityPoints(3, 2));

        // 4-unit course with Grade F (0 points) => 0 quality points
        $this->assertEquals(0, $this->service->calculateQualityPoints(0, 4));
    }

    public function test_semester_gpa_calculation(): void
    {
        $r1 = new Result(['credit_units' => 3, 'grade_point' => 5]); // 15
        $r2 = new Result(['credit_units' => 3, 'grade_point' => 4]); // 12
        $r3 = new Result(['credit_units' => 2, 'grade_point' => 3]); // 6
        $r4 = new Result(['credit_units' => 2, 'grade_point' => 0]); // 0

        // Total units = 3 + 3 + 2 + 2 = 10
        // Total points = 15 + 12 + 6 + 0 = 33
        // GPA = 33 / 10 = 3.30
        $calc = $this->service->calculateSemesterGpa(collect([$r1, $r2, $r3, $r4]));

        $this->assertEquals(10, $calc['total_units']);
        $this->assertEquals(33, $calc['total_points']);
        $this->assertEquals(3.30, $calc['semester_gpa']);
    }

    public function test_process_and_save_gpa_record(): void
    {
        $department = Department::first() ?? new Department();
        if (!$department->exists) {
            $department->name = 'Comp Sci ' . rand(100, 999);
            $department->save();
        }

        $programme = Programme::first() ?? new Programme();
        if (!$programme->exists) {
            $programme->name = 'Undergraduate ' . rand(100, 999);
            $programme->abv = 'UG';
            $programme->save();
        }

        $student = new User();
        $student->id = (string) \Illuminate\Support\Str::uuid();
        $student->programme_id = $programme->id;
        $student->email = 'student' . rand(1000, 9999) . '@test.com';
        $student->password = bcrypt('password');
        $student->vpassword = 'password';
        $student->role = 'student';
        $student->save();

        $course = new Course();
        $course->name = 'CSC101';
        $course->department_id = $department->id;
        $course->programme_id = $programme->id;
        $course->save();

        $level = StudentLevel::first() ?? new StudentLevel();
        if (!$level->exists) {
            $level->level = '100';
            $level->save();
        }

        $studentCourse = new StudentCourse();
        $studentCourse->code = 'CSC101' . rand(10, 99);
        $studentCourse->title = 'Intro to Programming ' . rand(10, 99);
        $studentCourse->units = '3';
        $studentCourse->student_level_id = $level->id;
        $studentCourse->semester = 1;
        $studentCourse->save();

        $deptCourse = new DepartmentCourse();
        $deptCourse->department_id = $department->id;
        $deptCourse->student_course_id = $studentCourse->id;
        $deptCourse->units = 3;
        $deptCourse->save();

        $academicDetail = new AcademicDetail();
        $academicDetail->user_id = $student->id;
        $academicDetail->matric_no = 'MAT/' . rand(1000, 9999);
        $academicDetail->course_id = $course->id;
        $academicDetail->programme_id = $programme->id;
        $academicDetail->department_id = $department->id;
        $academicDetail->student_level_id = $level->id;
        $academicDetail->acad_session = '2024-2025';
        $academicDetail->save();

        $reg = RegisteredCourse::create([
            'department_course_id' => $deptCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'student_level_id' => $level->id,
            'units' => '3',
            'academic_session' => '2024-2025',
        ]);

        Result::create([
            'user_id' => $student->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $deptCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'ca_score' => 35.0,
            'exam_score' => 50.0,
            'total_score' => 85.0,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'released',
        ]);

        $gpaRecord = $this->service->processAndSaveGpaRecord($student, '2024-2025', 'first');

        $this->assertNotNull($gpaRecord->id);
        $this->assertEquals(5.00, (float) $gpaRecord->semester_gpa);
        $this->assertEquals(3, $gpaRecord->total_credit_units);
        $this->assertEquals('First Class Honours', $gpaRecord->class_of_degree);
    }
}
