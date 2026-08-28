<?php

namespace Tests\Feature;

use App\Http\Livewire\Lecturer\LecturerDashboard;
use App\Http\Livewire\Lecturer\ResultEntry;
use App\Models\CourseAllocation;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class LecturerDashboardAllocationSessionTest extends TestCase
{
    use DatabaseTransactions;

    private User $lecturer;

    private CourseAllocation $allocation;

    private Department $department;

    private StudentLevel $level;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lecturer = User::create([
            'email' => 'lecturer_session_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Lecturer',
            'firstname' => 'Session',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
        ]);

        $this->department = new Department();
        $this->department->name = 'Session Test Department ' . uniqid();
        $this->department->code = 'S' . rand(10, 99);
        $this->department->save();

        $this->level = StudentLevel::first() ?? StudentLevel::create([
            'level' => '100',
        ]);

        $studentCourse = StudentCourse::create([
            'code' => 'TST' . rand(100, 999),
            'title' => 'Allocated Session Test Course',
            'units' => 3,
            'student_level_id' => $this->level->id,
            'semester' => 2,
        ]);

        $departmentCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);

        $this->allocation = CourseAllocation::create([
            'department_course_id' => $departmentCourse->id,
            'department_id' => $this->department->id,
            'lecturer_id' => $this->lecturer->id,
            'academic_session' => '2024/2025',
            'semester' => 'second',
            'assigned_units' => 3,
        ]);
    }

    public function test_lecturer_dashboard_lists_and_filters_by_allocation_sessions(): void
    {
        $sameLecturerCurrentCourse = StudentCourse::create([
            'code' => 'CUR' . rand(100, 999),
            'title' => 'Current Session Course',
            'units' => 3,
            'student_level_id' => $this->level->id,
            'semester' => 1,
        ]);

        $sameLecturerCurrentDepartmentCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $sameLecturerCurrentCourse->id,
            'units' => 3,
        ]);

        CourseAllocation::create([
            'department_course_id' => $sameLecturerCurrentDepartmentCourse->id,
            'department_id' => $this->department->id,
            'lecturer_id' => $this->lecturer->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'assigned_units' => 3,
        ]);

        $otherLecturer = User::create([
            'email' => 'other_lecturer_session_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'surname' => 'Other',
            'firstname' => 'Lecturer',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
        ]);

        $otherLecturerCourse = StudentCourse::create([
            'code' => 'OTH' . rand(100, 999),
            'title' => 'Other Lecturer Course',
            'units' => 3,
            'student_level_id' => $this->level->id,
            'semester' => 1,
        ]);

        $otherLecturerDepartmentCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $otherLecturerCourse->id,
            'units' => 3,
        ]);

        CourseAllocation::create([
            'department_course_id' => $otherLecturerDepartmentCourse->id,
            'department_id' => $this->department->id,
            'lecturer_id' => $otherLecturer->id,
            'academic_session' => '2024/2025',
            'semester' => 'first',
            'assigned_units' => 3,
        ]);

        $this->actingAs($this->lecturer);

        Livewire::test(LecturerDashboard::class)
            ->assertSee('2024/2025')
            ->assertSee('2025/2026')
            ->set('selectedSession', '2024/2025')
            ->assertSee('Allocated Session Test Course')
            ->assertSee('Second')
            ->assertDontSee('Current Session Course')
            ->assertDontSee('Other Lecturer Course')
            ->set('selectedSession', '2025/2026')
            ->assertSee('Current Session Course')
            ->assertDontSee('Allocated Session Test Course')
            ->assertDontSee('Other Lecturer Course');
    }

    public function test_result_entry_defaults_to_the_allocation_session_and_semester(): void
    {
        $this->actingAs($this->lecturer);

        Livewire::test(ResultEntry::class, ['courseAllocation' => $this->allocation])
            ->assertSet('selectedSession', '2024/2025')
            ->assertSet('selectedSemester', 'second')
            ->assertSet('availableSessions', ['2024/2025'])
            ->set('selectedSession', '2025/2026')
            ->assertSet('selectedSession', '2024/2025')
            ->set('selectedSemester', 'first')
            ->assertSet('selectedSemester', 'second');
    }
}
