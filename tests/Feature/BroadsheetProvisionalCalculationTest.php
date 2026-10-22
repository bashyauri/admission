<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\Result;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\ResultReportingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadsheetProvisionalCalculationTest extends TestCase
{
    use RefreshDatabase;

    private ResultReportingService $reporting;
    private Department $department;
    private StudentLevel $level1;
    private StudentLevel $level2;
    private User $student;
    private User $coordinator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reporting = app(ResultReportingService::class);

        $ugProgramme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate(['id' => ProgrammesEnum::Undergraduate->value, 'name' => 'BSc Computer Science', 'abv' => 'UG']);

        $this->department = Department::create(['name' => 'Computer Science']);

        $course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $ugProgramme->id,
            'semesters' => 8,
        ]);

        $this->level1 = StudentLevel::create(['level' => '100', 'programme_id' => $ugProgramme->id]);
        $this->level2 = StudentLevel::create(['level' => '200', 'programme_id' => $ugProgramme->id]);

        $this->student = User::factory()->create([
            'surname' => 'BELLO',
            'firstname' => 'FATIMA',
            'email' => 'fatima@example.com',
            'programme_id' => $ugProgramme->id,
        ]);

        $this->academicDetail = AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'UG/2024/001',
            'department_id' => $this->department->id,
            'programme_id' => $ugProgramme->id,
            'course_id' => $course->id,
            'student_level_id' => $this->level2->id,
            'admission_session' => '2024/2025',
        ]);

        $this->coordinator = User::factory()->create([
            'surname' => 'Coordinator',
            'firstname' => 'Staff',
            'email' => 'coordinator@example.com',
            'role' => 'coordinator',
        ]);
    }

    public function test_provisional_broadsheet_calculates_cgpa_including_unreleased_scores_and_cgpa_ls(): void
    {
        Model::preventLazyLoading(true);

        // 1. Year 1 (2024/2025): Released results (2 courses, 3 units each, score 70 => A = 5 points)
        // Total prior units = 6, total prior points = 30, prior CGPA = 5.00
        $sc1 = StudentCourse::create(['code' => 'CSC101', 'title' => 'Intro to CS', 'units' => 3, 'student_level_id' => $this->level1->id, 'semester' => 'first']);
        $dc1 = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $sc1->id,
            'semester' => 'first',
            'units' => 3,
        ]);
        $rc1 = \App\Models\RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $dc1->id,
            'student_level_id'     => $this->level1->id,
            'units'                => 3,
            'academic_session'     => '2024/2025',
        ]);
        Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $rc1->id,
            'department_course_id' => $dc1->id,
            'academic_session' => '2024/2025',
            'semester' => 'first',
            'ca_score' => 30,
            'exam_score' => 45,
            'total_score' => 75,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'course_code_snapshot' => 'CSC101',
            'course_title_snapshot' => 'Intro to CS',
            'credit_units_snapshot' => 3,
            'status' => 'released',
        ]);

        $sc2 = StudentCourse::create(['code' => 'CSC102', 'title' => 'Programming I', 'units' => 3, 'student_level_id' => $this->level1->id, 'semester' => 'second']);
        $dc2 = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $sc2->id,
            'semester' => 'second',
            'units' => 3,
        ]);
        $rc2 = \App\Models\RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $dc2->id,
            'student_level_id'     => $this->level1->id,
            'units'                => 3,
            'academic_session'     => '2024/2025',
        ]);
        Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $rc2->id,
            'department_course_id' => $dc2->id,
            'academic_session' => '2024/2025',
            'semester' => 'second',
            'ca_score' => 25,
            'exam_score' => 45,
            'total_score' => 70,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'course_code_snapshot' => 'CSC102',
            'course_title_snapshot' => 'Programming I',
            'credit_units_snapshot' => 3,
            'status' => 'released',
        ]);

        // 2. Year 2 (2025/2026): Unreleased review results (1 course, 4 units, score 55 => C = 3 points)
        // UTS = 4, GPTS = 12, GPA = 3.00
        // Expected Provisional UTD = 6 + 4 = 10
        // Expected Provisional GPTD = 30 + 12 = 42
        // Expected Provisional CGPA = 42 / 10 = 4.20
        // Expected CGPA LS = 5.00
        $sc3 = StudentCourse::create(['code' => 'CSC201', 'title' => 'Data Structures', 'units' => 4, 'student_level_id' => $this->level2->id, 'semester' => 'first']);
        $dc3 = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $sc3->id,
            'semester' => 'first',
            'units' => 4,
        ]);
        $rc3 = \App\Models\RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $dc3->id,
            'student_level_id'     => $this->level2->id,
            'units'                => 4,
            'academic_session'     => '2025/2026',
        ]);
        Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $rc3->id,
            'department_course_id' => $dc3->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'ca_score' => 20,
            'exam_score' => 35,
            'total_score' => 55,
            'grade' => 'C',
            'grade_point' => 3,
            'credit_units' => 4,
            'course_code_snapshot' => 'CSC201',
            'course_title_snapshot' => 'Data Structures',
            'credit_units_snapshot' => 4,
            'status' => 'submitted', // Coordinator review stage, NOT released!
        ]);

        $filters = [
            'department_id' => $this->department->id,
            'academic_session' => '2025/2026',
            'semester' => 'second',
            'student_level_id' => $this->level2->id,
            'released_only' => false,
            'final' => false,
        ];

        $data = $this->reporting->getDepartmentalBroadsheet($filters);
        $studentRow = collect($data['students'])->firstWhere('matric_no', 'UG/2024/001');

        $this->assertNotNull($studentRow);
        $this->assertEquals(4, $studentRow['uts']);
        $this->assertEquals(12, $studentRow['gpts']);
        $this->assertEquals(3.00, $studentRow['gpa']);
        $this->assertEquals(10, $studentRow['utd']);
        $this->assertEquals(42, $studentRow['gptd']);
        $this->assertEquals(4.20, $studentRow['cgpa']);
        $this->assertEquals('5.00', $studentRow['cgpa_ls']);
        $this->assertTrue($data['is_provisional']);
    }
}
