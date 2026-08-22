<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RedirectsSafetyTest extends TestCase
{
    public function test_student_redirect_after_auth_is_student_dashboard(): void
    {
        $student = User::create([
            'email' => 'student_redir_' . uniqid() . '@example.com',
            'role' => 'student',
            'surname' => 'Student',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        // When logged-in student visits guest routes (like login/register)
        $response = $this->actingAs($student)->get(route('login'));
        $response->assertRedirect(route('student.dashboard'));

        $student->delete();
    }

    public function test_applicant_redirect_after_auth_is_analytics(): void
    {
        $applicant = User::create([
            'email' => 'applicant_redir_' . uniqid() . '@example.com',
            'role' => 'applicant',
            'surname' => 'Applicant',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        // When logged-in applicant visits guest routes (like login/register)
        $response = $this->actingAs($applicant)->get(route('login'));
        $response->assertRedirect(route('analytics'));

        $applicant->delete();
    }

    public function test_applicant_blocked_from_lecturer_routes_is_redirected_to_analytics(): void
    {
        $applicant = User::create([
            'email' => 'applicant_redir2_' . uniqid() . '@example.com',
            'role' => 'applicant',
            'surname' => 'Applicant',
            'firstname' => 'Test',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($applicant)->get(route('lecturer.dashboard'));
        $response->assertRedirect(route('analytics'));

        $applicant->delete();
    }

    public function test_student_blocked_from_lecturer_routes_is_redirected_to_student_dashboard(): void
    {
        $student = User::create([
            'email' => 'student_redir2_' . uniqid() . '@example.com',
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
}
