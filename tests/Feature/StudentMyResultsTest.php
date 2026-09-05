<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Enums\Role;
use App\Http\Livewire\Student\MyResults;
use App\Models\AcademicDetail;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentMyResultsTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected Department $department;
    protected Programme $programme;
    protected StudentLevel $level;
    protected AcademicDetail $academicDetail;
    protected Course $course;
    protected StudentCourse $studentCourse1;
    protected StudentCourse $studentCourse2;
    protected DepartmentCourse $deptCourse1;
    protected DepartmentCourse $deptCourse2;
    protected RegisteredCourse $regCourse1;
    protected RegisteredCourse $regCourse2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::first() ?? Department::create(['name' => 'Computer Science ' . rand(100, 999)]);
        
        $this->programme = Programme::find(ProgrammesEnum::Undergraduate->value) 
            ?? Programme::create(['id' => ProgrammesEnum::Undergraduate->value, 'name' => 'Undergraduate', 'abv' => 'UG']);

        $this->level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);

        // Student
        $this->student = User::create([
            'email' => 'student_results_' . uniqid() . '@example.com',
            'role' => Role::STUDENT->value,
            'programme_id' => $this->programme->id,
            'surname' => 'Danjuma',
            'firstname' => 'Fatima',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        $this->course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $this->academicDetail = AcademicDetail::forceCreate([
            'user_id' => $this->student->id,
            'matric_no' => 'MAT/UG/' . rand(10000, 99999),
            'course_id' => $this->course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level->id,
        ]);

        // Create Courses
        $this->studentCourse1 = StudentCourse::create([
            'code' => 'CSC101',
            'title' => 'Introduction to Computer Science',
            'units' => 3,
            'semester' => 1,
            'student_level_id' => $this->level->id,
        ]);

        $this->deptCourse1 = DepartmentCourse::create([
            'student_course_id' => $this->studentCourse1->id,
            'department_id' => $this->department->id,
            'units' => 3,
        ]);

        $this->studentCourse2 = StudentCourse::create([
            'code' => 'CSC102',
            'title' => 'Introduction to Problem Solving',
            'units' => 2,
            'semester' => 1,
            'student_level_id' => $this->level->id,
        ]);

        $this->deptCourse2 = DepartmentCourse::create([
            'student_course_id' => $this->studentCourse2->id,
            'department_id' => $this->department->id,
            'units' => 2,
        ]);

        // Registrations
        $this->regCourse1 = RegisteredCourse::create([
            'academic_detail_id' => $this->academicDetail->id,
            'department_course_id' => $this->deptCourse1->id,
            'student_level_id' => $this->level->id,
            'units' => 3,
            'academic_session' => '2025/2026',
        ]);

        $this->regCourse2 = RegisteredCourse::create([
            'academic_detail_id' => $this->academicDetail->id,
            'department_course_id' => $this->deptCourse2->id,
            'student_level_id' => $this->level->id,
            'units' => 2,
            'academic_session' => '2025/2026',
        ]);
    }

    public function test_guest_cannot_access_my_results_page(): void
    {
        $response = $this->get(route('student.my-results'));
        $response->assertRedirect(route('home'));
    }

    public function test_undergraduate_student_can_render_my_results_component(): void
    {
        $this->actingAs($this->student);

        Livewire::test(MyResults::class)
            ->assertStatus(200)
            ->assertSee('My Academic Results')
            ->assertSee('Fatima')
            ->assertSee('Danjuma')
            ->assertSee($this->academicDetail->matric_no);
    }

    public function test_unreleased_results_are_hidden_from_student(): void
    {
        $this->actingAs($this->student);

        // Create a result with status 'submitted' or 'hod_approved' (unreleased)
        Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $this->regCourse1->id,
            'department_course_id' => $this->deptCourse1->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'course_code_snapshot' => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computer Science',
            'credit_units_snapshot' => 3,
            'ca_score' => 35,
            'exam_score' => 50,
            'total_score' => 85,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'submitted', // NOT released
        ]);

        Livewire::test(MyResults::class)
            ->assertStatus(200)
            ->assertSee('No Released Results Found')
            ->assertDontSee('CSC101');
    }

    public function test_released_results_are_visible_with_scores_grades_and_summary(): void
    {
        $this->actingAs($this->student);

        // Released Course 1 (CSC101: 85 -> A -> 5 GP * 3 Units = 15 QP)
        Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $this->regCourse1->id,
            'department_course_id' => $this->deptCourse1->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'course_code_snapshot' => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computer Science',
            'credit_units_snapshot' => 3,
            'ca_score' => 35,
            'exam_score' => 50,
            'total_score' => 85,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'released',
        ]);

        // Released Course 2 (CSC102: 65 -> B -> 4 GP * 2 Units = 8 QP)
        Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $this->regCourse2->id,
            'department_course_id' => $this->deptCourse2->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'course_code_snapshot' => 'CSC102',
            'course_title_snapshot' => 'Introduction to Problem Solving',
            'credit_units_snapshot' => 2,
            'ca_score' => 25,
            'exam_score' => 40,
            'total_score' => 65,
            'grade' => 'B',
            'grade_point' => 4,
            'credit_units' => 2,
            'grade_point_total' => 8,
            'status' => 'released',
        ]);

        // Create GPA Record: TCR = 5, TQP = 23, GPA = 4.60
        ResultGpaRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'semester_gpa' => 4.60,
            'total_credit_units' => 5,
            'total_grade_points' => 23,
            'cumulative_gpa' => 4.60,
            'cumulative_credit_units' => 5,
            'cumulative_grade_points' => 23,
            'class_of_degree' => 'First Class Honours',
        ]);

        Livewire::test(MyResults::class)
            ->assertStatus(200)
            ->assertSee('CSC101')
            ->assertSee('CSC102')
            ->assertSee('Introduction to Computer Science')
            ->assertSee('Introduction to Problem Solving')
            ->assertSee('85.0')
            ->assertSee('65.0')
            ->assertSee('4.60')
            ->assertSee('First Class Honours');
    }

    public function test_session_filtering_works_correctly(): void
    {
        $this->actingAs($this->student);

        // Result in 2024/2025
        Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $this->regCourse1->id,
            'department_course_id' => $this->deptCourse1->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'semester' => 'first',
            'course_code_snapshot' => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computer Science',
            'credit_units_snapshot' => 3,
            'ca_score' => 30,
            'exam_score' => 40,
            'total_score' => 70,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'released',
        ]);

        // Result in 2025/2026
        Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $this->regCourse2->id,
            'department_course_id' => $this->deptCourse2->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'course_code_snapshot' => 'CSC102',
            'course_title_snapshot' => 'Introduction to Problem Solving',
            'credit_units_snapshot' => 2,
            'ca_score' => 28,
            'exam_score' => 42,
            'total_score' => 70,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 2,
            'grade_point_total' => 10,
            'status' => 'released',
        ]);

        // Filtering by 2025/2026 only
        Livewire::test(MyResults::class)
            ->set('selectedSession', '2025/2026')
            ->assertSee('CSC102')
            ->assertDontSee('2024/2025 Academic Session');
    }
}
