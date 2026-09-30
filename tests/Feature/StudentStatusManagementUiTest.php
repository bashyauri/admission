<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
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

    public function test_admin_can_create_recommendation_and_submit_senate_decision_through_ui_actions(): void
    {
        $component = Livewire::actingAs($this->admin)
            ->test(StudentStatusManagement::class)
            ->set('studentSearch', 'UG/STATUS/UI-001')
            ->call('selectStudent', (string) $this->student->id)
            ->set('reasonCode', 'CONSECUTIVE_PROBATION')
            ->set('reason', 'Academic review recommendation')
            ->set('academicSession', '2025/2026')
            ->set('effectiveDate', '2026-09-01')
            ->call('createStatusRecord')
            ->assertHasNoErrors();

        $recommendation = StudentStatusRecord::query()->where('user_id', $this->student->id)->firstOrFail();
        $this->assertSame(StudentStatusService::WORKFLOW_RECOMMENDED, $recommendation->senate_decision);

        $component->call('submitRecommendation', $recommendation->id)
            ->call('beginSenateDecision', $recommendation->id, true)
            ->set('senateReference', 'SEN-2026-901')
            ->set('senateDecisionDate', '2026-09-10')
            ->call('decideWithdrawal')
            ->assertHasNoErrors();

        $this->assertSame(StudentStatusService::WORKFLOW_SENATE_APPROVED, $recommendation->fresh()->senate_decision);
        $this->assertSame('SEN-2026-901', $recommendation->fresh()->senate_reference);
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
