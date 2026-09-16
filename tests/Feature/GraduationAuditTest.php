<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\ExamOfficer\GraduationAudit;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\GraduationEligibility;
use App\Models\GraduationList;
use App\Models\GraduationListItem;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\GraduationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class GraduationAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $examOfficer;
    protected User $student;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
    protected StudentLevel $level400;
    protected AcademicDetail $academicDetail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->programme = Programme::create([
            'name' => 'B.Sc Computer Science ' . uniqid(),
            'abv' => 'UG',
        ]);

        $this->department = Department::create([
            'name' => 'Computer Science Dept ' . uniqid(),
        ]);

        $this->course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $this->level400 = StudentLevel::where('level', '400')->first() ?? StudentLevel::create(['level' => '400']);

        $this->examOfficer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'examofficer_' . uniqid() . '@example.com',
            'role' => 'admin',
            'surname' => 'Officer',
            'firstname' => 'Exam',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->student = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'student_' . uniqid() . '@example.com',
            'role' => 'student',
            'surname' => 'Graduand',
            'firstname' => 'Test',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->academicDetail = AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'MAT/TEST/' . rand(1000, 9999),
            'course_id' => $this->course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level400->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2020/2021',
        ]);
    }

    public function test_unauthorized_student_cannot_access_graduation_audit(): void
    {
        $response = $this->actingAs($this->student)->get(route('exam-officer.graduation-audit'));
        $response->assertRedirect();
    }

    public function test_exam_officer_can_access_graduation_audit_page(): void
    {
        $response = $this->actingAs($this->examOfficer)->get(route('exam-officer.graduation-audit'));
        $response->assertOk();
        $response->assertSee('Exam Officer Graduation Audit & Clearance');
        $response->assertSee('Run Cohort Audit');
        $response->assertSee('Batch Clear Eligible');
        $response->assertSee('Stage to Pass List');
    }

    public function test_livewire_component_mounts_and_renders_candidate(): void
    {
        Livewire::actingAs($this->examOfficer)
            ->test(GraduationAudit::class)
            ->set('selectedDepartmentId', $this->department->id)
            ->set('selectedSession', '2024/2025')
            ->assertSee($this->academicDetail->matric_no)
            ->assertSee('Test Graduand')
            ->assertSee('Not Audited');
    }

    public function test_run_cohort_audit_evaluates_and_updates_candidate_status(): void
    {
        Livewire::actingAs($this->examOfficer)
            ->test(GraduationAudit::class)
            ->set('selectedDepartmentId', $this->department->id)
            ->set('selectedSession', '2024/2025')
            ->call('runCohortAudit')
            ->assertDispatchedBrowserEvent('alert');

        $this->assertDatabaseHas('graduation_eligibilities', [
            'user_id' => $this->student->id,
            'academic_session' => '2024/2025',
        ]);
    }

    public function test_exam_officer_can_clear_eligible_candidate(): void
    {
        $eligibility = GraduationEligibility::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'final_cgpa' => 3.80,
            'class_of_degree' => 'Second Class Honours (Upper Division)',
            'total_units_earned' => 125,
            'total_units_required' => 120,
            'meets_requirements' => true,
            'siwes_completed' => true,
            'general_studies_completed' => true,
            'entrepreneurship_completed' => true,
            'is_cleared' => false,
        ]);

        Livewire::actingAs($this->examOfficer)
            ->test(GraduationAudit::class)
            ->set('selectedDepartmentId', $this->department->id)
            ->set('selectedSession', '2024/2025')
            ->call('openClearModal', $eligibility->id)
            ->assertSet('showClearModal', true)
            ->assertSet('clearingEligibilityId', $eligibility->id)
            ->call('confirmClearStudent')
            ->assertSet('showClearModal', false);

        $this->assertDatabaseHas('graduation_eligibilities', [
            'id' => $eligibility->id,
            'is_cleared' => 1,
            'cleared_by' => $this->examOfficer->id,
        ]);
    }

    public function test_stage_to_graduation_list(): void
    {
        $eligibility = GraduationEligibility::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'final_cgpa' => 4.25,
            'class_of_degree' => 'First Class Honours',
            'total_units_earned' => 130,
            'total_units_required' => 120,
            'meets_requirements' => true,
            'siwes_completed' => true,
            'general_studies_completed' => true,
            'entrepreneurship_completed' => true,
            'is_cleared' => true,
            'cleared_by' => $this->examOfficer->id,
            'cleared_at' => now(),
        ]);

        Livewire::actingAs($this->examOfficer)
            ->test(GraduationAudit::class)
            ->set('selectedDepartmentId', $this->department->id)
            ->set('selectedSession', '2024/2025')
            ->call('stageToGraduationList', $eligibility->id);

        $this->assertDatabaseHas('graduation_list_items', [
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'matric_no' => $this->academicDetail->matric_no,
        ]);
    }
}
