<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Enums\Role;
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
use Tests\TestCase;

/**
 * Feature tests for Phase 5 Task 2:
 * Semester Statement of Result — Printable Slip (Undergraduate only).
 *
 * Route: GET /student/print-statement/{session}/{semester}
 * Middleware: web, Authenticate, EnsureEmailIsVerified, RoleMiddleware:student
 */
class SemesterStatementOfResultTest extends TestCase
{
    use RefreshDatabase;

    protected User $ugStudent;
    protected Department $department;
    protected Programme $programme;
    protected StudentLevel $level;
    protected AcademicDetail $academicDetail;
    protected Course $course;
    protected DepartmentCourse $deptCourse1;
    protected DepartmentCourse $deptCourse2;
    protected RegisteredCourse $regCourse1;
    protected RegisteredCourse $regCourse2;

    /** @var Result */
    protected Result $result1;
    /** @var Result */
    protected Result $result2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::first()
            ?? Department::create(['name' => 'CS Department ' . rand(100, 999)]);

        $this->programme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate([
                'id'   => ProgrammesEnum::Undergraduate->value,
                'name' => 'Undergraduate',
                'abv'  => 'UG',
            ]);

        $this->level = StudentLevel::first()
            ?? StudentLevel::create(['level' => '100']);

        // Note: email_verified_at must be set so EnsureEmailIsVerified middleware passes
        $this->ugStudent = User::create([
            'email'             => 'ug_sor_' . uniqid() . '@example.com',
            'role'              => Role::STUDENT->value,
            'programme_id'      => $this->programme->id,
            'surname'           => 'Suleiman',
            'firstname'         => 'Aisha',
            'password'          => bcrypt('password'),
            'vpassword'         => 'password',
            'email_verified_at' => now(),
        ]);

        $this->course = Course::create([
            'name'          => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id'  => $this->programme->id,
        ]);

        $this->academicDetail = AcademicDetail::forceCreate([
            'user_id'          => $this->ugStudent->id,
            'matric_no'        => 'UG/' . rand(10000, 99999),
            'course_id'        => $this->course->id,
            'programme_id'     => $this->programme->id,
            'department_id'    => $this->department->id,
            'student_level_id' => $this->level->id,
        ]);

        $sc1 = StudentCourse::create([
            'code'             => 'CSC101',
            'title'            => 'Introduction to Computing',
            'units'            => 3,
            'semester'         => 1,
            'student_level_id' => $this->level->id,
        ]);

        $sc2 = StudentCourse::create([
            'code'             => 'MTH101',
            'title'            => 'Elementary Mathematics',
            'units'            => 2,
            'semester'         => 1,
            'student_level_id' => $this->level->id,
        ]);

        $this->deptCourse1 = DepartmentCourse::create([
            'student_course_id' => $sc1->id,
            'department_id'     => $this->department->id,
            'units'             => 3,
        ]);

        $this->deptCourse2 = DepartmentCourse::create([
            'student_course_id' => $sc2->id,
            'department_id'     => $this->department->id,
            'units'             => 2,
        ]);

        // registered_courses unique: (academic_detail_id, department_course_id, academic_session)
        $this->regCourse1 = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $this->deptCourse1->id,
            'student_level_id'     => $this->level->id,
            'units'                => 3,
            'academic_session'     => '2025/2026',
        ]);

        $this->regCourse2 = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $this->deptCourse2->id,
            'student_level_id'     => $this->level->id,
            'units'                => 2,
            'academic_session'     => '2025/2026',
        ]);

        // Released results for 2025/2026 First semester
        $this->result1 = Result::forceCreate([
            'user_id'               => $this->ugStudent->id,
            'registered_course_id'  => $this->regCourse1->id,
            'department_course_id'  => $this->deptCourse1->id,
            'academic_session'      => '2025/2026',
            'semester'              => 'first',
            'ca_score'              => 35,
            'exam_score'            => 52,
            'total_score'           => 87,
            'grade'                 => 'A',
            'grade_point'           => 5,
            'credit_units_snapshot' => 3,
            'course_code_snapshot'  => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computing',
            'status'                => 'released',
        ]);

        $this->result2 = Result::forceCreate([
            'user_id'               => $this->ugStudent->id,
            'registered_course_id'  => $this->regCourse2->id,
            'department_course_id'  => $this->deptCourse2->id,
            'academic_session'      => '2025/2026',
            'semester'              => 'first',
            'ca_score'              => 28,
            'exam_score'            => 38,
            'total_score'           => 66,
            'grade'                 => 'B',
            'grade_point'           => 4,
            'credit_units_snapshot' => 2,
            'course_code_snapshot'  => 'MTH101',
            'course_title_snapshot' => 'Elementary Mathematics',
            'status'                => 'released',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Access control
    // ──────────────────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_statement_of_result(): void
    {
        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'first',
        ]));

        // Guests are redirected (to login / home)
        $response->assertRedirect();
    }

    public function test_postgraduate_student_is_forbidden(): void
    {
        $pgProgramme = Programme::where('id', '!=', ProgrammesEnum::Undergraduate->value)->first()
            ?? Programme::forceCreate(['id' => ProgrammesEnum::PG->value, 'name' => 'Postgraduate', 'abv' => 'PG']);

        $pgStudent = User::create([
            'email'             => 'pg_sor_' . uniqid() . '@example.com',
            'role'              => Role::STUDENT->value,
            'programme_id'      => $pgProgramme->id,
            'surname'           => 'Musa',
            'firstname'         => 'Ibrahim',
            'password'          => bcrypt('password'),
            'vpassword'         => 'password',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($pgStudent);

        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'first',
        ]));

        $response->assertForbidden();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Happy path: slip renders with correct data
    // ──────────────────────────────────────────────────────────────────────────

    public function test_statement_renders_for_valid_session_and_semester(): void
    {
        $this->actingAs($this->ugStudent);

        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'first',
        ]));

        $response->assertOk();

        // Document heading
        $response->assertSee('SEMESTER STATEMENT OF RESULT', false);

        // Student info
        $response->assertSee($this->academicDetail->matric_no, false);
        $response->assertSee('Suleiman', false);

        // Course data
        $response->assertSee('CSC101', false);
        $response->assertSee('Introduction to Computing', false);
        $response->assertSee('MTH101', false);

        // Grade column header
        $response->assertSee('Grade', false);
    }

    public function test_statement_shows_correct_gpa_when_gpa_record_exists(): void
    {
        $this->actingAs($this->ugStudent);

        // TCR = 3+2 = 5, TQP = (5*3)+(4*2) = 23, GPA = 23/5 = 4.60
        ResultGpaRecord::forceCreate([
            'user_id'                   => $this->ugStudent->id,
            'academic_session'          => '2025/2026',
            'semester'                  => 'first',
            'semester_gpa'              => 4.60,
            'cumulative_gpa'            => 4.60,
            'total_credit_units'        => 5,
            'total_grade_points'        => 23,
            'cumulative_credit_units'   => 5,
            'cumulative_grade_points'   => 23,
        ]);

        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'first',
        ]));

        $response->assertOk();
        $response->assertSee('4.60', false);
    }

    public function test_statement_includes_nuc_grading_key(): void
    {
        $this->actingAs($this->ugStudent);

        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'first',
        ]));

        $response->assertOk();
        $response->assertSee('NUC Undergraduate Grading Key', false);
        $response->assertSee('Excellent', false);  // Grade A description
        $response->assertSee('Grade Point', false);
    }

    public function test_statement_includes_attestation_and_signature_areas(): void
    {
        $this->actingAs($this->ugStudent);

        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'first',
        ]));

        $response->assertOk();
        $response->assertSee('Head of Department', false);
        $response->assertSee('Registrar', false);
        $response->assertSee("Student's Signature", false);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Edge cases
    // ──────────────────────────────────────────────────────────────────────────

    public function test_statement_returns_404_for_semester_with_no_results(): void
    {
        $this->actingAs($this->ugStudent);

        // No second-semester results exist in setUp
        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'second',
        ]));

        $response->assertNotFound();
    }

    public function test_statement_returns_404_for_invalid_session_format(): void
    {
        $this->actingAs($this->ugStudent);

        // The route constraint only allows [0-9]{4}-[0-9]{4}
        $response = $this->get('/student/print-statement/badSession/first');
        $response->assertNotFound();
    }

    public function test_statement_shows_fail_remark_for_failed_course(): void
    {
        // Add a second-semester RegisteredCourse (different dept course avoids unique constraint)
        $scFail = StudentCourse::create([
            'code'             => 'CSC201',
            'title'            => 'Data Structures',
            'units'            => 3,
            'semester'         => 2,
            'student_level_id' => $this->level->id,
        ]);

        $deptCourseFail = DepartmentCourse::create([
            'student_course_id' => $scFail->id,
            'department_id'     => $this->department->id,
            'units'             => 3,
        ]);

        $regCourseFail = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $deptCourseFail->id,
            'student_level_id'     => $this->level->id,
            'units'                => 3,
            'academic_session'     => '2025/2026',
        ]);

        Result::forceCreate([
            'user_id'               => $this->ugStudent->id,
            'registered_course_id'  => $regCourseFail->id,
            'department_course_id'  => $deptCourseFail->id,
            'academic_session'      => '2025/2026',
            'semester'              => 'second',
            'ca_score'              => 10,
            'exam_score'            => 20,
            'total_score'           => 30,
            'grade'                 => 'F',
            'grade_point'           => 0,
            'credit_units_snapshot' => 3,
            'course_code_snapshot'  => 'CSC201',
            'course_title_snapshot' => 'Data Structures',
            'status'                => 'released',
        ]);

        $this->actingAs($this->ugStudent);

        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'second',
        ]));

        $response->assertOk();
        $response->assertSee('FAIL', false);
    }

    public function test_reference_number_is_present_on_statement(): void
    {
        $this->actingAs($this->ugStudent);

        $response = $this->get(route('student.print-statement-of-result', [
            'session'  => '2025-2026',
            'semester' => 'first',
        ]));

        $response->assertOk();
        // Reference number format: SOR-{MATRIC}-20252026-FIR
        $response->assertSee('SOR-', false);
    }
}
