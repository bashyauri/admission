<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCapability;
use Tests\TestCase;

/**
 * Day 5 Verification Suite
 *
 * End-to-end checks that validate the complete multi-role capability system
 * is working correctly, including:
 * - Standalone Lecturer and Exam Officer portals
 * - HOD role-switching via capabilities
 * - Cross-portal access restrictions
 * - Sidebar switcher logic (via canActAs* helpers)
 */
class Day5VerificationTest extends TestCase
{
    // ---------------------------------------------------------------
    // 5.1 Standalone Role Portals
    // ---------------------------------------------------------------

    public function test_standalone_lecturer_redirects_to_lecturer_dashboard_on_login(): void
    {
        $lecturer = User::create([
            'email'             => 'day5_lec_' . uniqid() . '@example.com',
            'role'              => 'lecturer',
            'surname'           => 'Day5',
            'firstname'         => 'Lecturer',
            'password'          => bcrypt('secret'),
            'vpassword'         => 'secret',
            'email_verified_at' => now(),
        ]);

        // Posting to login should redirect to lecturer dashboard
        $response = $this->post(route('login'), [
            'email'    => $lecturer->email,
            'password' => 'secret',
        ]);

        $response->assertRedirect(route('lecturer.dashboard'));

        $lecturer->delete();
    }

    public function test_standalone_exam_officer_redirects_to_exam_officer_dashboard_on_login(): void
    {
        $examOfficer = User::create([
            'email'             => 'day5_eo_' . uniqid() . '@example.com',
            'role'              => 'exam_officer',
            'surname'           => 'Day5',
            'firstname'         => 'ExamOfficer',
            'password'          => bcrypt('secret'),
            'vpassword'         => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('login'), [
            'email'    => $examOfficer->email,
            'password' => 'secret',
        ]);

        $response->assertRedirect(route('exam-officer.dashboard'));

        $examOfficer->delete();
    }

    // ---------------------------------------------------------------
    // 5.2 HOD Capability Switcher
    // ---------------------------------------------------------------

    public function test_hod_with_both_capabilities_can_access_lecturer_and_exam_officer_portals(): void
    {
        $hod = User::create([
            'email'             => 'day5_hod_' . uniqid() . '@example.com',
            'role'              => 'hod',
            'surname'           => 'Day5',
            'firstname'         => 'HOD',
            'password'          => bcrypt('secret'),
            'vpassword'         => 'secret',
            'email_verified_at' => now(),
        ]);

        UserCapability::create([
            'user_id'    => $hod->id,
            'capability' => 'lecturer',
            'is_active'  => true,
            'reason'     => 'Day5 test',
        ]);

        UserCapability::create([
            'user_id'    => $hod->id,
            'capability' => 'exam_officer',
            'is_active'  => true,
            'reason'     => 'Day5 test',
        ]);

        // HOD can still access their own dashboard
        $this->actingAs($hod)->get(route('hod.dashboard'))->assertOk();

        // HOD can switch to Lecturer portal
        $this->actingAs($hod)->get(route('lecturer.dashboard'))
            ->assertOk()
            ->assertSee('Lecturer Dashboard');

        // HOD can switch to Exam Officer portal
        $this->actingAs($hod)->get(route('exam-officer.dashboard'))
            ->assertOk()
            ->assertSee('Exam Officer Dashboard');

        $hod->delete();
    }

    public function test_hod_capability_helpers_reflect_granted_capabilities(): void
    {
        $hod = User::create([
            'email'             => 'day5_cap_' . uniqid() . '@example.com',
            'role'              => 'hod',
            'surname'           => 'Day5',
            'firstname'         => 'Cap',
            'password'          => bcrypt('secret'),
            'vpassword'         => 'secret',
            'email_verified_at' => now(),
        ]);

        // Before any capability
        $this->assertFalse($hod->canActAsLecturer());
        $this->assertFalse($hod->canActAsExamOfficer());

        UserCapability::create([
            'user_id'    => $hod->id,
            'capability' => 'lecturer',
            'is_active'  => true,
            'reason'     => 'Day5 helper test',
        ]);

        $hod->refresh();
        $this->assertTrue($hod->canActAsLecturer());
        $this->assertFalse($hod->canActAsExamOfficer());

        $hod->delete();
    }

    // ---------------------------------------------------------------
    // 5.3 Cross-Portal Access Restrictions
    // ---------------------------------------------------------------

    public function test_pure_lecturer_cannot_access_hod_routes(): void
    {
        $lecturer = User::create([
            'email'             => 'day5_noauth_' . uniqid() . '@example.com',
            'role'              => 'lecturer',
            'surname'           => 'Day5',
            'firstname'         => 'NoAuth',
            'password'          => bcrypt('secret'),
            'vpassword'         => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)->get(route('hod.dashboard'));
        // Should be redirected away — not OK
        $response->assertRedirect();
        $this->assertNotEquals(200, $response->status());

        $lecturer->delete();
    }

    public function test_pure_lecturer_cannot_access_admin_routes(): void
    {
        $lecturer = User::create([
            'email'             => 'day5_noadmin_' . uniqid() . '@example.com',
            'role'              => 'lecturer',
            'surname'           => 'Day5',
            'firstname'         => 'NoAdmin',
            'password'          => bcrypt('secret'),
            'vpassword'         => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)->get(route('admin.dashboard'));
        $response->assertRedirect();
        $this->assertNotEquals(200, $response->status());

        $lecturer->delete();
    }

    public function test_revoked_capability_blocks_access(): void
    {
        $hod = User::create([
            'email'             => 'day5_revoked_' . uniqid() . '@example.com',
            'role'              => 'hod',
            'surname'           => 'Day5',
            'firstname'         => 'Revoked',
            'password'          => bcrypt('secret'),
            'vpassword'         => 'secret',
            'email_verified_at' => now(),
        ]);

        $cap = UserCapability::create([
            'user_id'    => $hod->id,
            'capability' => 'lecturer',
            'is_active'  => false, // Revoked
            'reason'     => 'Day5 revoked test',
        ]);

        // With is_active = false the HOD should NOT have the capability
        $hod->refresh();
        $this->assertFalse($hod->canActAsLecturer());

        // And should be redirected when trying to access lecturer portal
        $this->actingAs($hod)->get(route('lecturer.dashboard'))->assertRedirect();

        $hod->delete();
    }
}
