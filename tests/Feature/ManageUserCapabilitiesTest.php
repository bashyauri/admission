<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\Admin\ManageUserCapabilities;
use App\Models\Department;
use App\Models\Programme;
use App\Models\User;
use App\Models\UserCapability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ManageUserCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    protected Programme $programme;
    protected Department $department;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->programme = Programme::create([
            'name' => 'B.Sc Test Programme',
            'abv' => 'UG',
        ]);

        $this->department = Department::create([
            'name' => 'Science Laboratory Technology',
        ]);

        $this->admin = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'admin_' . uniqid() . '@example.com',
            'role' => 'admin',
            'surname' => 'Admin',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_view_manage_capabilities_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.manage-capabilities'));
        $response->assertOk();
        $response->assertSee('Staff Capabilities');
        $response->assertSee('Active HODs');
        $response->assertSee('HOD (Head of Dept)');
    }

    public function test_modal_displays_hod_capability_option(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->call('openAssignModal')
            ->assertSeeHtml('value="hod"')
            ->assertSee('Head of Dept / Endorse results');
    }

    public function test_modal_displays_granular_student_status_permissions_without_wildcard(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->call('openAssignModal')
            ->assertSee('Student Status Permissions')
            ->assertSeeHtml('value="student_status.recommend"')
            ->assertSeeHtml('value="student_status.senate_decide"')
            ->assertSeeHtml('value="disciplinary_actions.manage"')
            ->assertSeeHtml('value="student_status.audit.view"')
            ->assertDontSeeHtml('value="student_status.*"');
    }

    public function test_admin_can_grant_department_scoped_student_status_capability(): void
    {
        $staff = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'status_staff_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Status',
            'firstname' => 'Officer',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->set('selectedUserId', $staff->id)
            ->set('capability', 'student_status.recommend')
            ->set('departmentId', $this->department->id)
            ->set('reason', 'Authorized department withdrawal recommendations')
            ->call('assignCapability')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('user_capabilities', [
            'user_id' => $staff->id,
            'capability' => 'student_status.recommend',
            'department_id' => $this->department->id,
            'granted_by' => $this->admin->id,
            'reason' => 'Authorized department withdrawal recommendations',
            'is_active' => true,
        ]);
    }

    public function test_student_status_audit_capability_cannot_be_department_scoped(): void
    {
        $staff = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'audit_staff_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Audit',
            'firstname' => 'Officer',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->set('selectedUserId', $staff->id)
            ->set('capability', 'student_status.audit.view')
            ->set('departmentId', $this->department->id)
            ->call('assignCapability')
            ->assertHasErrors(['departmentId']);

        $this->assertDatabaseMissing('user_capabilities', [
            'user_id' => $staff->id,
            'capability' => 'student_status.audit.view',
        ]);
    }

    public function test_wildcard_student_status_capability_is_rejected(): void
    {
        $staff = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'wildcard_staff_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Wildcard',
            'firstname' => 'Officer',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->set('selectedUserId', $staff->id)
            ->set('capability', 'student_status.*')
            ->call('assignCapability')
            ->assertHasErrors(['capability']);

        $this->assertDatabaseMissing('user_capabilities', [
            'user_id' => $staff->id,
            'capability' => 'student_status.*',
        ]);
    }

    public function test_admin_can_assign_capability_to_user_via_livewire(): void
    {
        $lecturer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'staff_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Staff',
            'firstname' => 'Member',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->set('selectedUserId', $lecturer->id)
            ->set('capability', 'exam_officer')
            ->set('departmentId', $this->department->id)
            ->set('reason', 'Appointed 2025/2026')
            ->call('assignCapability')
            ->assertHasNoErrors();

        $this->assertTrue(
            UserCapability::where('user_id', $lecturer->id)
                ->where('capability', 'exam_officer')
                ->where('is_active', true)
                ->exists()
        );

        $lecturer->refresh();
        $this->assertTrue($lecturer->canActAsExamOfficer());
    }

    public function test_admin_can_toggle_and_revoke_capability(): void
    {
        $staff = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'staff_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Staff',
            'firstname' => 'Toggle',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $cap = UserCapability::create([
            'user_id' => $staff->id,
            'capability' => 'exam_officer',
            'is_active' => true,
            'reason' => 'Initial',
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->call('toggleStatus', $cap->id);

        $cap->refresh();
        $this->assertFalse($cap->is_active);

        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->call('revokeCapability', $cap->id);

        $this->assertDatabaseMissing('user_capabilities', ['id' => $cap->id]);
    }

    public function test_assigning_hod_capability_syncs_hod_user_and_enables_switch_to_hod(): void
    {
        $lecturer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'lecturer_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Lecturer',
            'firstname' => 'John',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->set('selectedUserId', $lecturer->id)
            ->set('capability', 'hod')
            ->set('departmentId', $this->department->id)
            ->set('reason', 'Appointed HOD Science Laboratory Technology')
            ->call('assignCapability')
            ->assertHasNoErrors();

        $lecturer->refresh();
        $this->assertTrue($lecturer->canActAsHod());
        $this->assertNotNull($lecturer->hodDetails);
        $this->assertEquals($this->department->id, $lecturer->hodDetails->department_id);

        $cap = UserCapability::where('user_id', $lecturer->id)->where('capability', 'hod')->first();
        $this->assertNotNull($cap);

        // Revoking HOD capability removes HodUser
        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->call('revokeCapability', $cap->id);

        $lecturer->refresh();
        $this->assertNull($lecturer->hodDetails);
        $this->assertFalse($lecturer->canActAsHod());
    }

    public function test_modal_staff_live_search_and_selection_works(): void
    {
        $lecturer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'zubairu_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Zubairu',
            'firstname' => 'Ibrahim',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManageUserCapabilities::class)
            ->call('openAssignModal')
            ->set('staffSearch', 'Zubairu')
            ->assertSee('Zubairu Ibrahim')
            ->call('selectStaff', $lecturer->id)
            ->assertSet('selectedUserId', $lecturer->id)
            ->assertSee('Selected')
            ->call('clearSelectedStaff')
            ->assertSet('selectedUserId', '');
    }
}
