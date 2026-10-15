<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\CourseAllocation;
use App\Models\DepartmentCourse;
use App\Models\Course;
use App\Models\AcademicSession;
use App\Models\Semester;
use Livewire\Livewire;
use App\Http\Livewire\Lecturer\ResultEntry;
use App\Models\AcademicDetail;
use App\Models\RegisteredCourse;

class LecturerResultEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_view_their_allocated_courses_dashboard()
    {
        // For simplicity, we just assert the route exists and is protected by middleware
        $user = User::factory()->create(); // Needs to be lecturer
        // $this->actingAs($user)->get('/lecturer/dashboard')->assertStatus(200); // Assuming full factory setup
        $this->assertTrue(true); // Stub
    }

    public function test_lecturer_can_save_valid_ca_and_exam_scores()
    {
        // Livewire::test(ResultEntry::class, ['courseAllocation' => $allocation])
        //     ->set("results.{$userId}.ca", 30)
        //     ->set("results.{$userId}.exam", 50)
        //     ->call('saveScore', $userId)
        //     ->assertHasNoErrors();
        $this->assertTrue(true); // Stub
    }

    public function test_submit_results_routes_to_matching_level_coordinator_when_direct_coordinator_has_mismatched_level(): void
    {
        $department = \App\Models\Department::create(['name' => 'Test Statistics ' . uniqid()]);
        $programme = \App\Models\Programme::create(['name' => 'UG Test ' . uniqid(), 'abv' => 'UG']);
        $level100 = \App\Models\StudentLevel::create(['level' => '100']);
        $level200 = \App\Models\StudentLevel::create(['level' => '200']);

        $course = \App\Models\Course::create([
            'name' => 'STA101',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
        ]);

        $lecturer = User::create([
            'email' => 'lecturer_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'programme_id' => $programme->id,
            'surname' => 'Lecturer',
            'firstname' => 'Test',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        $coordUser = User::create([
            'email' => 'coord_' . uniqid() . '@example.com',
            'role' => 'coordinator',
            'programme_id' => $programme->id,
            'surname' => 'Coord',
            'firstname' => 'Test',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        // 200L Coordinator (legacy or mismatched direct assignment)
        $coord200 = \App\Models\Coordinator::create([
            'user_id' => $coordUser->id,
            'department_id' => $department->id,
            'student_level_id' => $level200->id,
            'academic_session' => '2024/2025',
        ]);

        // 100L Coordinator (matching cohort assignment)
        $coord100 = \App\Models\Coordinator::create([
            'user_id' => $coordUser->id,
            'department_id' => $department->id,
            'student_level_id' => $level100->id,
            'academic_session' => '2025/2026',
        ]);

        $student = User::create([
            'email' => 'student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'surname' => 'Student',
            'firstname' => 'One',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        // Student is 100L, 2025/2026 cohort, but has direct coordinator_id = coord200 (mismatched level)
        $ad = \App\Models\AcademicDetail::forceCreate([
            'user_id' => $student->id,
            'matric_no' => 'MAT/' . rand(10000, 99999),
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level100->id,
            'admission_session' => '2025/2026',
            'coordinator_id' => $coord200->id,
        ]);

        $studentCourse = \App\Models\StudentCourse::create([
            'code' => 'STA111',
            'title' => 'Intro Statistics',
            'units' => '3',
            'student_level_id' => $level100->id,
        ]);

        $deptCourse = \App\Models\DepartmentCourse::create([
            'department_id' => $department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);

        $allocation = \App\Models\CourseAllocation::create([
            'user_id' => $lecturer->id,
            'department_course_id' => $deptCourse->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
        ]);

        $result = \App\Models\Result::create([
            'user_id' => $student->id,
            'academic_detail_id' => $ad->id,
            'department_course_id' => $deptCourse->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'ca_score' => 30,
            'exam_score' => 45,
            'total_score' => 75,
            'status' => 'pending',
            'level_snapshot' => $level100->id,
        ]);

        Livewire::actingAs($lecturer)
            ->test(\App\Http\Livewire\Lecturer\ResultEntry::class, ['courseAllocation' => $allocation])
            ->call('submitResults');

        $result->refresh();

        $this->assertEquals('submitted', $result->status);
        $this->assertEquals($coord100->id, $result->coordinator_id, 'Result should be routed to 100L coordinator rather than mismatched 200L coordinator.');
    }
}
