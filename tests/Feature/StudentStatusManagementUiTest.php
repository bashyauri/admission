<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\StudentStatusRecord;
use App\Models\User;
use App\Models\UserCapability;
use App\Services\StudentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Http\Livewire\Student\StudentStatusManagement;
use App\Http\Livewire\Student\StudentStatusOverview;

class StudentStatusManagementUiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $programme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate([
                'id' => ProgrammesEnum::Undergraduate->value,
                'name' => 'Undergraduate',
                'abv' => 'UG',
            ]);
        $department = Department::create(['name' => 'Status UI Department']);
        $course = Course::create([
            'name' => 'Status UI Course',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semesters' => 8,
        ]);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->student = User::factory()->create([
            'role' => 'student',
            'programme_id' => $programme->id,
        ]);
        AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'UG/STATUS/UI-001',
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);
    }

    public function test_admin_can_record_a_senate_approved_withdrawal_in_one_submission(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StudentStatusManagement::class)
            ->set('studentSearch', 'UG/STATUS/UI-001')
            ->call('selectStudent', (string) $this->student->id)
            ->set('reasonCode', 'CONSECUTIVE_PROBATION')
            ->set('reason', 'Senate approved the withdrawal recommendation.')
            ->set('academicSession', '2025/2026')
            ->set('effectiveDate', '2026-09-01')
            ->set('senateReference', 'SEN-2026-902')
            ->set('senateDecisionDate', '2026-09-10')
            ->call('createStatusRecord')
            ->assertHasNoErrors();

        $record = StudentStatusRecord::query()->where('user_id', $this->student->id)->firstOrFail();
        $this->assertSame(StudentStatusService::WORKFLOW_SENATE_APPROVED, $record->senate_decision);
        $this->assertSame('SEN-2026-902', $record->senate_reference);
        $this->assertSame('2026-09-10', $record->senate_decision_date->toDateString());

        $actions = \Illuminate\Support\Facades\DB::table('student_status_audits')
            ->where('student_id', $this->student->id)
            ->pluck('action')
            ->all();
        $this->assertContains('WITHDRAWAL_RECOMMENDED', $actions);
        $this->assertContains('WITHDRAWAL_SUBMITTED_FOR_SENATE', $actions);
        $this->assertContains('WITHDRAWAL_APPROVED', $actions);
    }


    public function test_admin_must_supply_senate_reference_and_date_for_single_action(): void
    {
        Livewire::actingAs($this->admin)
            ->test(StudentStatusManagement::class)
            ->set('studentSearch', 'UG/STATUS/UI-001')
            ->call('selectStudent', (string) $this->student->id)
            ->set('reasonCode', 'CONSECUTIVE_PROBATION')
            ->set('reason', 'Senate review required.')
            ->set('academicSession', '2025/2026')
            ->set('effectiveDate', '2026-09-01')
            ->call('createStatusRecord')
            ->assertHasErrors(['senateReference', 'senateDecisionDate']);

        $this->assertDatabaseMissing('student_status_records', ['user_id' => $this->student->id]);
    }

    public function test_admin_can_see_students_due_for_withdrawal_review_queue(): void
    {
        config(['academic_withdrawal.consecutive_probation' => [
            'enabled' => true,
            'threshold' => 2,
            'unit' => 'session',
            'reason_code' => 'CONSECUTIVE_PROBATION',
            'reason' => 'Student has been on academic probation for {threshold} consecutive sessions.',
        ]]);

        $eligibleStudent = User::factory()->create([
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'firstname' => 'Due',
            'surname' => 'Review',
        ]);

        $detail = AcademicDetail::create([
            'user_id' => $eligibleStudent->id,
            'matric_no' => 'UG/STATUS/REVIEW-001',
            'course_id' => Course::query()->first()->id,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'department_id' => Department::query()->first()->id,
            'student_level_id' => StudentLevel::first()->id,
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        AcademicProgressionRecord::create([
            'user_id' => $eligibleStudent->id,
            'academic_detail_id' => $detail->id,
            'academic_session' => '2024/2025',
            'semester' => 2,
            'level' => '100',
            'cgpa' => 1.20,
            'standing' => 'PROBATION',
            'withdrawal_recommended' => false,
        ]);

        AcademicProgressionRecord::create([
            'user_id' => $eligibleStudent->id,
            'academic_detail_id' => $detail->id,
            'academic_session' => '2025/2026',
            'semester' => 2,
            'level' => '100',
            'cgpa' => 1.10,
            'standing' => 'PROBATION',
            'withdrawal_recommended' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(StudentStatusManagement::class)
            ->assertSee('Due for withdrawal review')
            ->assertSee('Review');
    }

    public function test_student_search_matches_numeric_matric_number_tokens(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'firstname' => 'Numeric',
            'surname' => 'Search',
        ]);

        AcademicDetail::create([
            'user_id' => $student->id,
            'matric_no' => 'UG/25/10905041',
            'course_id' => Course::query()->first()->id,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'department_id' => Department::query()->first()->id,
            'student_level_id' => StudentLevel::first()->id,
            'acad_session' => '2025/2026',
            'admission_session' => '2025/2026',
        ]);

        Livewire::actingAs($this->admin)
            ->test(StudentStatusManagement::class)
            ->set('studentSearch', '2510905041')
            ->assertSee('Numeric')
            ->assertSee('Search');
    }

    public function test_staff_without_status_view_capability_cannot_open_management_ui(): void
    {
        $staff = User::factory()->create(['role' => 'hod']);

        Livewire::actingAs($staff)
            ->test(StudentStatusManagement::class)
            ->assertForbidden();
    }

    public function test_status_view_capability_does_not_grant_senate_decision_authority(): void
    {
        $recommendation = app(\App\Services\StudentStatusService::class)->createWithdrawalRecommendation(
            user: $this->student,
            reasonCode: 'CONSECUTIVE_PROBATION',
            reason: 'Academic review recommendation',
            academicSession: '2025/2026',
            processedBy: $this->admin,
        );
        app(\App\Services\StudentStatusService::class)->submitForSenate($recommendation, $this->admin);

        $viewer = User::factory()->create(['role' => 'hod']);
        UserCapability::create([
            'user_id' => $viewer->id,
            'capability' => 'student_status.view',
            'is_active' => true,
        ]);

        Livewire::actingAs($viewer)
            ->test(StudentStatusManagement::class)
            ->call('beginSenateDecision', $recommendation->id, true)
            ->assertForbidden();
    }

    public function test_student_can_view_own_status_overview_and_historical_record_links(): void
    {
        Livewire::actingAs($this->student)
            ->test(StudentStatusOverview::class)
            ->assertSee('My Student Status')
            ->assertSee('My results')
            ->assertSee('Transcript')
            ->assertSee('Course history');
    }

    public function test_department_scoped_policies_do_not_lazy_load_student_academic_detail(): void
    {
        $viewer = User::factory()->create(['role' => 'hod']);
        UserCapability::create([
            'user_id' => $viewer->id,
            'capability' => 'student_status.view',
            'department_id' => $this->student->academicDetail()->value('department_id'),
            'is_active' => true,
        ]);

        $disciplinaryManager = User::factory()->create(['role' => 'hod']);
        UserCapability::create([
            'user_id' => $disciplinaryManager->id,
            'capability' => 'disciplinary_actions.manage',
            'department_id' => $this->student->academicDetail()->value('department_id'),
            'is_active' => true,
        ]);

        $statusPolicy = app(\App\Policies\StudentStatusPolicy::class);
        $disciplinaryPolicy = app(\App\Policies\DisciplinaryActionPolicy::class);

        $this->assertTrue($statusPolicy->view($viewer, $this->student));
        $this->assertTrue($disciplinaryPolicy->manage($disciplinaryManager, $this->student));
    }

    public function test_staff_management_route_does_not_select_the_student_sidebar(): void
    {
        $this->actingAs($this->admin)
            ->get(route('student-status.management', ['sidebar' => 'admin']))
            ->assertOk()
            ->assertSee('Course Allocations')
            ->assertDontSee('My Results');
    }

    public function test_other_authorized_staff_roles_keep_their_sidebar_on_the_management_route(): void
    {
        foreach ([
            ['hod', 'hod', 'Result Approvals'],
            ['coordinator', 'coordinator', 'Result Review'],
            ['lecturer', 'lecturer', 'Lecturer Dashboard'],
            ['cit', 'cit', 'Course Allocations'],
        ] as [$role, $sidebar, $sidebarLabel]) {
            $staff = User::factory()->create(['role' => $role]);
            UserCapability::create([
                'user_id' => $staff->id,
                'capability' => 'student_status.view',
                'is_active' => true,
            ]);

            $this->actingAs($staff)
                ->get(route('student-status.management', ['sidebar' => $sidebar]))
                ->assertOk()
                ->assertSee($sidebarLabel)
                ->assertDontSee('My Results');
        }
    }
}
