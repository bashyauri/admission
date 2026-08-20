<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\User;
use App\Models\UserCapability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleCapabilityTest extends TestCase
{
    public function test_role_enum_contains_all_nine_roles(): void
    {
        $roles = Role::getRoles();
        
        $this->assertCount(9, $roles);
        
        $expectedValues = [
            'hod',
            'applicant',
            'admin',
            'student',
            'cit',
            'coordinator',
            'idcard_officer',
            'lecturer',
            'exam_officer',
        ];

        $actualValues = array_column($roles, 'value');
        $this->assertEquals($expectedValues, $actualValues);

        $this->assertEquals('Lecturer', Role::LECTURER->toString());
        $this->assertEquals('Exam Officer', Role::EXAM_OFFICER->toString());
    }

    public function test_pure_lecturer_role_checks(): void
    {
        $user = new User(['role' => 'lecturer']);

        $this->assertTrue($user->isLecturer());
        $this->assertTrue($user->canActAsLecturer());
        $this->assertFalse($user->isExamOfficer());
        $this->assertFalse($user->canActAsExamOfficer());
    }

    public function test_pure_exam_officer_role_checks(): void
    {
        $user = new User(['role' => 'exam_officer']);

        $this->assertTrue($user->isExamOfficer());
        $this->assertTrue($user->canActAsExamOfficer());
        $this->assertFalse($user->isLecturer());
        $this->assertFalse($user->canActAsLecturer());
    }

    public function test_hod_with_lecturer_capability(): void
    {
        $user = User::create([
            'email' => 'hod_test_' . uniqid() . '@example.com',
            'role' => 'hod',
            'surname' => 'Test',
            'firstname' => 'HOD',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
        ]);

        $this->assertTrue($user->isHod());
        $this->assertFalse($user->isLecturer());
        $this->assertFalse($user->canActAsLecturer());

        UserCapability::create([
            'user_id' => $user->id,
            'capability' => 'lecturer',
            'is_active' => true,
            'reason' => 'Test capability assignment',
        ]);

        $user->refresh();

        $this->assertTrue($user->isHod());
        $this->assertFalse($user->isLecturer());
        $this->assertTrue($user->hasCapability('lecturer'));
        $this->assertTrue($user->canActAsLecturer());
        $this->assertFalse($user->canActAsExamOfficer());

        // Cleanup
        $user->delete();
    }
}
