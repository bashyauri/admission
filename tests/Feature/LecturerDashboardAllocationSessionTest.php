<?php

namespace Tests\Feature;

use App\Http\Livewire\Lecturer\LecturerDashboard;
use App\Http\Livewire\Lecturer\ResultEntry;
use App\Models\AcademicDetail;
use App\Models\CourseAllocation;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LecturerDashboardAllocationSessionTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    private CourseAllocation $allocation;

    private Department $department;

    private StudentLevel $level;

    private Programme $programme;

    protected function setUp(): void
    {
        parent::setUp();

        $this->programme = Programme::first() ?? Programme::create([
            'name' => 'Undergraduate Test Programme',
            'abv' => 'UG',
        ]);

        $this->lecturer = User::create([
            'programme_id' => $this->programme->id,
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
            'programme_id' => $this->programme->id,
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
            ->assertSee('second')
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

    public function test_result_entry_loads_registered_students_in_matric_number_order(): void
    {
        $course = Course::create([
            'name' => 'Result Entry Test Course',
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
        ]);

        $registrations = [];

        foreach (['UG/2025/002', 'UG/2025/001'] as $index => $matricNumber) {
            $student = User::create([
                'programme_id' => $this->programme->id,
                'surname' => 'Student',
                'firstname' => 'Test ' . $index,
                'email' => 'result_entry_student_' . uniqid() . '@example.com',
                'role' => 'student',
                'password' => bcrypt('secret'),
                'vpassword' => 'secret',
            ]);

            $academicDetail = AcademicDetail::create([
                'user_id' => $student->id,
                'matric_no' => $matricNumber,
                'course_id' => $course->id,
                'programme_id' => $this->programme->id,
                'department_id' => $this->department->id,
                'student_level_id' => $this->level->id,
            ]);

            $registrations[] = RegisteredCourse::create([
                'department_course_id' => $this->allocation->department_course_id,
                'academic_detail_id' => $academicDetail->id,
                'student_level_id' => $this->level->id,
                'units' => 3,
                'academic_session' => $this->allocation->academic_session,
            ]);
        }

        $this->actingAs($this->lecturer);

        $component = Livewire::test(ResultEntry::class, ['courseAllocation' => $this->allocation]);

        $students = $component->get('students');
        $this->assertSame(
            ['UG/2025/001', 'UG/2025/002'],
            $students->pluck('academicDetail.matric_no')->all(),
        );
        $this->assertSame(
            [$registrations[1]->id, $registrations[0]->id],
            $students->pluck('id')->all(),
        );
    }
}
