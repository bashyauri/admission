<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Http\Livewire\ExamOfficer\ManageDisciplinaryActions;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryActionAudit;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class ExamOfficerDisciplinaryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_officer_can_view_disciplinary_management_page(): void
    {
        $programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $department = Department::create([
            'name' => 'Computer Science Department',
            'faculty' => 'Faculty of Science',
        ]);

        $level = StudentLevel::firstOrCreate(['level' => '300']);

        $course = Course::create([
            'name' => 'Principles of Software Engineering',
            'code' => 'CSC 301',
            'credit_unit' => 3,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semester' => 'first',
            'level' => '300',
            'is_active' => true,
        ]);

        $examOfficer = User::create([
            'email' => 'exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Amina',
            'surname' => 'Okafor',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $student = User::create([
            'email' => 'disciplinary_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Ada',
            'surname' => 'Ife',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'course_id' => $course->id,
            'student_level_id' => $level->id,
            'matric_no' => 'UG/25/CS/1001',
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        DisciplinaryAction::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'sanction_type' => 'repeat_session',
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'senate_ref_no' => 'SEN-2026-042',
            'verdict_date' => now()->toDateString(),
            'effective_session' => '2025/2026',
            'is_active' => true,
            'is_appealed' => false,
            'sanctioned_by' => $examOfficer->id,
            'remarks' => 'Repeat session sanction.',
        ]);

        $response = $this->actingAs($examOfficer)->get(route('exam-officer.disciplinary-actions'));

        $response->assertStatus(200);
        $response->assertSee('Disciplinary Sanctions');
        $response->assertSee('SEN-2026-042');

        Livewire::test(ManageDisciplinaryActions::class)
            ->set('searchQuery', 'Ada')
            ->assertSee('Ada');
    }

    public function test_exam_officer_can_search_student_by_numeric_matric_number(): void
    {
        $programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $department = Department::create([
            'name' => 'Computer Science Department',
            'faculty' => 'Faculty of Science',
        ]);

        $level = StudentLevel::firstOrCreate(['level' => '300']);

        $examOfficer = User::create([
            'email' => 'matric_search_exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Grace',
            'surname' => 'Musa',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $student = User::create([
            'email' => 'matric_search_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Kelechi',
            'surname' => 'Nwosu',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'course_id' => Course::create([
                'name' => 'Operating Systems',
                'code' => 'CSC 302',
                'credit_unit' => 3,
                'department_id' => $department->id,
                'programme_id' => $programme->id,
                'semester' => 'first',
                'level' => '300',
                'is_active' => true,
            ])->id,
            'student_level_id' => $level->id,
            'matric_no' => 'UG/25/10905041',
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        Livewire::actingAs($examOfficer)
            ->test(ManageDisciplinaryActions::class)
            ->set('searchQuery', '2510905041')
            ->assertSee('Kelechi')
            ->assertSee('Nwosu');
    }

    public function test_exam_officer_can_open_apply_modal_for_student_with_integer_ids(): void
    {
        $programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $department = Department::create([
            'name' => 'Computer Science Department',
            'faculty' => 'Faculty of Science',
        ]);

        $level = StudentLevel::firstOrCreate(['level' => '300']);

        $examOfficer = User::create([
            'email' => 'modal_exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'James',
            'surname' => 'Nwachukwu',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $student = User::create([
            'email' => 'modal_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Tunde',
            'surname' => 'Adebayo',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'course_id' => Course::create([
                'name' => 'Database Design',
                'code' => 'CSC 303',
                'credit_unit' => 3,
                'department_id' => $department->id,
                'programme_id' => $programme->id,
                'semester' => 'first',
                'level' => '300',
                'is_active' => true,
            ])->id,
            'student_level_id' => $level->id,
            'matric_no' => 'UG/25/90010014',
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        Livewire::actingAs($examOfficer)
            ->test(ManageDisciplinaryActions::class)
            ->call('openApplyModal', $student->id)
            ->assertSet('selectedStudentId', $student->id)
            ->assertSet('selectedAcademicDetailId', $academicDetail->id)
            ->assertSet('showApplyModal', true);
    }

    public function test_exam_officer_can_manage_disciplinary_actions_by_role(): void
    {
        $programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $department = Department::create([
            'name' => 'Computer Science Department',
            'faculty' => 'Faculty of Science',
        ]);

        $level = StudentLevel::firstOrCreate(['level' => '300']);

        $examOfficer = User::create([
            'email' => 'apply_exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Ruth',
            'surname' => 'Eze',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $student = User::create([
            'email' => 'apply_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Emeka',
            'surname' => 'Ugo',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'course_id' => Course::create([
                'name' => 'Compiler Construction',
                'code' => 'CSC 304',
                'credit_unit' => 3,
                'department_id' => $department->id,
                'programme_id' => $programme->id,
                'semester' => 'first',
                'level' => '300',
                'is_active' => true,
            ])->id,
            'student_level_id' => $level->id,
            'matric_no' => 'UG/25/90900020',
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        $this->assertTrue(Gate::forUser($examOfficer)->allows('disciplinary-actions.manage', $student));
    }

    public function test_exam_officer_can_lift_active_sanction_using_valid_senate_reference(): void
    {
        $programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $department = Department::create([
            'name' => 'Computer Science Department',
            'faculty' => 'Faculty of Science',
        ]);

        $level = StudentLevel::firstOrCreate(['level' => '300']);
        $course = Course::create([
            'name' => 'Computer Architecture',
            'code' => 'CSC 305',
            'credit_unit' => 3,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semester' => 'first',
            'level' => '300',
            'is_active' => true,
        ]);

        $examOfficer = User::create([
            'email' => 'lift_exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Musa',
            'surname' => 'Bello',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $student = User::create([
            'email' => 'lift_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Aisha',
            'surname' => 'Sule',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'course_id' => $course->id,
            'student_level_id' => $level->id,
            'matric_no' => 'UG/25/90800022',
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        $action = DisciplinaryAction::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'sanction_type' => 'repeat_session',
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'senate_ref_no' => 'SEN-2026-100',
            'verdict_date' => now()->toDateString(),
            'effective_session' => '2025/2026',
            'is_active' => true,
            'is_appealed' => false,
            'sanctioned_by' => $examOfficer->id,
            'remarks' => 'Repeat session sanction.',
        ]);

        DisciplinaryActionAudit::create([
            'disciplinary_action_id' => $action->id,
            'student_id' => $student->id,
            'actor_id' => $examOfficer->id,
            'event' => 'SANCTION_APPLIED',
            'metadata' => ['effect' => ['progression_records' => [], 'result_attempts' => []]],
            'occurred_at' => now(),
            'notes' => 'Applied sanction for repeated session.',
        ]);

        Livewire::actingAs($examOfficer)
            ->test(ManageDisciplinaryActions::class)
            ->call('liftSanction', $action->id, app(\App\Services\DisciplinaryActionService::class));

        $this->assertFalse($action->fresh()->is_active);
    }

    public function test_exam_officer_page_renders_for_records_with_loaded_academic_detail(): void
    {
        $programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $department = Department::create([
            'name' => 'Computer Science Department',
            'faculty' => 'Faculty of Science',
        ]);

        $level = StudentLevel::firstOrCreate(['level' => '300']);
        $course = Course::create([
            'name' => 'Computer Architecture',
            'code' => 'CSC 305',
            'credit_unit' => 3,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semester' => 'first',
            'level' => '300',
            'is_active' => true,
        ]);

        $examOfficer = User::create([
            'email' => 'render_exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Musa',
            'surname' => 'Bello',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $student = User::create([
            'email' => 'render_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Yahaya',
            'surname' => 'Ibrahim',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'course_id' => $course->id,
            'student_level_id' => $level->id,
            'matric_no' => '2510905041',
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        DisciplinaryAction::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'sanction_type' => 'repeat_session',
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'senate_ref_no' => 'SEN-2026-001',
            'verdict_date' => now()->toDateString(),
            'effective_session' => '2025/2026',
            'is_active' => true,
            'is_appealed' => false,
            'sanctioned_by' => $examOfficer->id,
            'remarks' => 'Repeat session sanction.',
        ]);

        Livewire::actingAs($examOfficer)
            ->test(ManageDisciplinaryActions::class)
            ->assertOk()
            ->assertSee('Yahaya Ibrahim')
            ->assertSee('2510905041');
    }

    public function test_exam_officer_can_open_disciplinary_detail_modal(): void
    {
        $programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $department = Department::create([
            'name' => 'Computer Science Department',
            'faculty' => 'Faculty of Science',
        ]);

        $level = StudentLevel::firstOrCreate(['level' => '300']);
        $course = Course::create([
            'name' => 'Computer Architecture',
            'code' => 'CSC 305',
            'credit_unit' => 3,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semester' => 'first',
            'level' => '300',
            'is_active' => true,
        ]);

        $examOfficer = User::create([
            'email' => 'detail_exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Musa',
            'surname' => 'Bello',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $student = User::create([
            'email' => 'detail_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Aisha',
            'surname' => 'Sule',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'course_id' => $course->id,
            'student_level_id' => $level->id,
            'matric_no' => 'UG/25/90800099',
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        $action = DisciplinaryAction::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'sanction_type' => 'repeat_session',
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'senate_ref_no' => 'SEN-2026-001',
            'verdict_date' => now()->toDateString(),
            'effective_session' => '2025/2026',
            'is_active' => true,
            'is_appealed' => false,
            'sanctioned_by' => $examOfficer->id,
            'remarks' => 'Repeat session sanction.',
        ]);

        Livewire::actingAs($examOfficer)
            ->test(ManageDisciplinaryActions::class)
            ->call('viewDetail', $action->id)
            ->assertSet('showDetailModal', true)
            ->assertSet('selectedActionId', $action->id);
    }

    public function test_exam_officer_session_filter_uses_registered_course_sessions(): void
    {
        $programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $department = Department::create([
            'name' => 'Computer Science Department',
            'faculty' => 'Faculty of Science',
        ]);

        $level = StudentLevel::firstOrCreate(['level' => '300']);
        $course = Course::create([
            'name' => 'Principles of Software Engineering',
            'code' => 'CSC 301',
            'credit_unit' => 3,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semester' => 'first',
            'level' => '300',
            'is_active' => true,
        ]);
        $studentCourse = StudentCourse::create([
            'code' => 'CSC 301',
            'title' => 'Principles of Software Engineering',
            'units' => 3,
            'student_level_id' => $level->id,
            'semester' => 'first',
            'max_ca' => 40,
            'max_exam' => 60,
        ]);

        $departmentCourse = DepartmentCourse::create([
            'student_course_id' => $studentCourse->id,
            'department_id' => $department->id,
            'units' => 3,
        ]);

        $examOfficer = User::create([
            'email' => 'session_exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Bola',
            'surname' => 'Ade',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $student = User::create([
            'email' => 'registered_session_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Tunde',
            'surname' => 'Salami',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'course_id' => $course->id,
            'student_level_id' => $level->id,
            'matric_no' => 'UG/25/CS/1002',
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        RegisteredCourse::create([
            'academic_detail_id' => $academicDetail->id,
            'department_course_id' => $departmentCourse->id,
            'student_level_id' => $level->id,
            'units' => '3',
            'academic_session' => '2026/2027',
        ]);

        DisciplinaryAction::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'sanction_type' => 'repeat_session',
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'senate_ref_no' => 'SEN-2026-043',
            'verdict_date' => now()->toDateString(),
            'effective_session' => '2025/2026',
            'is_active' => true,
            'is_appealed' => false,
            'sanctioned_by' => $examOfficer->id,
            'remarks' => 'Repeat session sanction for earlier session.',
        ]);

        Livewire::actingAs($examOfficer)
            ->test(ManageDisciplinaryActions::class)
            ->assertSet('availableSessions', ['2026/2027'])
            ->assertSet('selectedSession', '2026/2027');
    }
}
