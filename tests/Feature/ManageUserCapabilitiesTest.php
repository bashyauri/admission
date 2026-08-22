<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\ManageUserCapabilities;
use App\Models\Department;
use App\Models\User;
use App\Models\UserCapability;
use Livewire\Livewire;
use Tests\TestCase;

class ManageUserCapabilitiesTest extends TestCase
{
    public function test_admin_can_view_manage_capabilities_page(): void
    {
        $admin = User::create([
            'email' => 'admin_' . uniqid() . '@example.com',
            'role' => 'admin',
            'surname' => 'Admin',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.manage-capabilities'));
        $response->assertOk();
        $response->assertSee('Staff Capabilities & Role Assignments');

        $admin->delete();
    }

    public function test_admin_can_assign_capability_to_user_via_livewire(): void
    {
        $admin = User::create([
            'email' => 'admin_' . uniqid() . '@example.com',
            'role' => 'admin',
            'surname' => 'Admin',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $lecturer = User::create([
            'email' => 'staff_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Staff',
            'firstname' => 'Member',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $dept = Department::first();

        Livewire::actingAs($admin)
            ->test(ManageUserCapabilities::class)
            ->set('selectedUserId', $lecturer->id)
            ->set('capability', 'exam_officer')
            ->set('departmentId', $dept?->id)
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

        $admin->delete();
        $lecturer->delete();
    }

    public function test_admin_can_toggle_and_revoke_capability(): void
    {
        $admin = User::create([
            'email' => 'admin_' . uniqid() . '@example.com',
            'role' => 'admin',
            'surname' => 'Admin',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $staff = User::create([
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

        Livewire::actingAs($admin)
            ->test(ManageUserCapabilities::class)
            ->call('toggleStatus', $cap->id);

        $cap->refresh();
        $this->assertFalse($cap->is_active);

        Livewire::actingAs($admin)
            ->test(ManageUserCapabilities::class)
            ->call('revokeCapability', $cap->id);

        $this->assertDatabaseMissing('user_capabilities', ['id' => $cap->id]);

        $admin->delete();
        $staff->delete();
    }
}
