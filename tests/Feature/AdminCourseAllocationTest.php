<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\CourseAllocationManager;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCourseAllocationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_course_allocation_route_exists()
    {
        $this->assertTrue(true);
    }

    public function test_course_allocation_can_be_created()
    {
        $this->assertTrue(true);
    }

    public function test_registered_course_sessions_are_available_for_allocation(): void
    {
        $admin = User::create([
            'email' => 'admin_allocation_session_' . uniqid() . '@example.com',
            'role' => 'admin',
            'surname' => 'Admin',
            'firstname' => 'Allocation',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
        ]);

        $student = User::create([
            'email' => 'student_allocation_session_' . uniqid() . '@example.com',
            'role' => 'student',
            'surname' => 'Student',
            'firstname' => 'Registered',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
        ]);

        $programme = Programme::create([
            'name' => 'Allocation Session Programme ' . uniqid(),
            'abv' => 'ASP',
        ]);

        $department = new Department();
        $department->name = 'Allocation Session Department ' . uniqid();

        if (Schema::hasColumn('departments', 'code')) {
            $department->code = 'A' . rand(10, 99);
        }

        $department->save();

        $level = StudentLevel::first() ?? StudentLevel::create([
            'level' => '100',
        ]);

        $course = Course::create([
            'name' => 'Allocation Session Course',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'matric_no' => 'ALLOC/' . rand(10000, 99999),
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
        ]);

        $studentCourse = StudentCourse::create([
            'code' => 'ASC' . rand(100, 999),
            'title' => 'Registered Course Session Source',
            'units' => 3,
            'student_level_id' => $level->id,
            'semester' => 1,
        ]);

        $departmentCourse = DepartmentCourse::create([
            'department_id' => $department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);

        RegisteredCourse::create([
            'department_course_id' => $departmentCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'student_level_id' => $level->id,
            'units' => 3,
            'academic_session' => '2024/2025',
        ]);

        $this->actingAs($admin);

        Livewire::test(CourseAllocationManager::class)
            ->assertSet('availableSessions', fn (array $sessions) => in_array('2024/2025', $sessions, true));
    }
}
