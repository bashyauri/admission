<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCapability;
use Tests\TestCase;

class CapabilityMiddlewareRoutingTest extends TestCase
{
    public function test_guest_is_redirected_to_home_when_accessing_lecturer_dashboard(): void
    {
        $response = $this->get(route('lecturer.dashboard'));
        $response->assertRedirect(route('home'));
    }

    public function test_pure_lecturer_can_access_lecturer_dashboard(): void
    {
        $lecturer = User::create([
            'email' => 'lecturer_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Lecturer',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)->get(route('lecturer.dashboard'));
        $response->assertOk();
        $response->assertSee('Lecturer Dashboard');

        $lecturer->delete();
    }

    public function test_hod_with_lecturer_capability_can_access_lecturer_dashboard(): void
    {
        $hod = User::create([
            'email' => 'hod_' . uniqid() . '@example.com',
            'role' => 'hod',
            'surname' => 'HOD',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        UserCapability::create([
            'user_id' => $hod->id,
            'capability' => 'lecturer',
            'is_active' => true,
            'reason' => 'HOD teaches course',
        ]);

        $response = $this->actingAs($hod)->get(route('lecturer.dashboard'));
        $response->assertOk();
        $response->assertSee('Lecturer Dashboard');

        $hod->delete();
    }

    public function test_student_is_redirected_away_from_lecturer_dashboard(): void
    {
        $student = User::create([
            'email' => 'student_' . uniqid() . '@example.com',
            'role' => 'student',
            'surname' => 'Student',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($student)->get(route('lecturer.dashboard'));
        $response->assertRedirect(route('student.dashboard'));

        $student->delete();
    }

    public function test_pure_exam_officer_can_access_exam_officer_dashboard(): void
    {
        $examOfficer = User::create([
            'email' => 'exam_officer_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'surname' => 'Exam',
            'firstname' => 'Officer',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($examOfficer)->get(route('exam-officer.dashboard'));
        $response->assertOk();
        $response->assertSee('Exam Officer Dashboard');

        $examOfficer->delete();
    }

    public function test_lecturer_without_exam_officer_capability_is_redirected_to_lecturer_dashboard(): void
    {
        $lecturer = User::create([
            'email' => 'lecturer_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Lecturer',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($lecturer)->get(route('exam-officer.dashboard'));
        $response->assertRedirect(route('lecturer.dashboard'));

        $lecturer->delete();
    }

    public function test_admin_bypasses_capability_check(): void
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

        $response = $this->actingAs($admin)->get(route('lecturer.dashboard'));
        $response->assertOk();

        $response2 = $this->actingAs($admin)->get(route('exam-officer.dashboard'));
        $response2->assertOk();

        $admin->delete();
    }
}
