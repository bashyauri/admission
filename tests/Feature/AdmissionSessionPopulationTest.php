<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\AdmissionSessionSynchronizer;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdmissionSessionPopulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_synchronize_admission_session_from_matric_number(): void
    {
        $academicDetail = $this->createAcademicDetail(
            '24' . random_int(10000000, 99999999)
        );

        Livewire::test(AdmissionSessionSynchronizer::class)
            ->set('selectedAcademicDetailIds', [$academicDetail->id])
            ->call('synchronizeSelected');

        $this->assertSame('2024/2025', $academicDetail->fresh()->admission_session);
    }

    public function test_unparseable_matric_numbers_are_not_synchronized(): void
    {
        $academicDetail = $this->createAcademicDetail('MAT/2410901001');

        Livewire::test(AdmissionSessionSynchronizer::class)
            ->set('selectedAcademicDetailIds', [$academicDetail->id])
            ->call('synchronizeSelected');

        $this->assertNull($academicDetail->fresh()->admission_session);
    }

    public function test_coordinator_resolution_is_cohort_specific_across_multiple_admission_sessions(): void
    {
        $department = Department::first() ?? Department::create([
            'name' => 'Cohort Dept ' . uniqid(),
        ]);
        $programme = Programme::first() ?? Programme::create([
            'name' => 'Cohort Prog ' . uniqid(),
            'abv' => 'CP',
        ]);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);
        $course = Course::create([
            'name' => 'Cohort Course ' . uniqid(),
            'department_id' => $department->id,
            'programme_id' => $programme->id,
        ]);

        // Create 2 Coordinators for 2 different admission sessions for the exact same course and level
        $userCoordA = User::create([
            'email' => 'coord_24_' . uniqid() . '@example.com',
            'role' => 'coordinator',
            'programme_id' => $programme->id,
            'surname' => 'Coordinator',
            'firstname' => '2024-Session',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        $coordinator2024 = \App\Models\Coordinator::create([
            'user_id' => $userCoordA->id,
            'course_id' => $course->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'academic_session' => '2024/2025',
        ]);

        $userCoordB = User::create([
            'email' => 'coord_25_' . uniqid() . '@example.com',
            'role' => 'coordinator',
            'programme_id' => $programme->id,
            'surname' => 'Coordinator',
            'firstname' => '2025-Session',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        $coordinator2025 = \App\Models\Coordinator::create([
            'user_id' => $userCoordB->id,
            'course_id' => $course->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'academic_session' => '2025/2026',
        ]);

        // Student A: Admitted in 2024/2025
        $studentA = User::create([
            'email' => 'student_24_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'surname' => 'Student',
            'firstname' => 'Cohort2024',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        $academicDetailA = AcademicDetail::forceCreate([
            'user_id' => $studentA->id,
            'matric_no' => '241090' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2026/2027',
            'admission_session' => '2024/2025',
        ]);

        // Student B: Admitted in 2025/2026
        $studentB = User::create([
            'email' => 'student_25_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'surname' => 'Student',
            'firstname' => 'Cohort2025',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        $academicDetailB = AcademicDetail::forceCreate([
            'user_id' => $studentB->id,
            'matric_no' => '251090' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2026/2027',
            'admission_session' => '2025/2026',
        ]);

        // Prove cohort-specific isolation:
        $resolvedA = $academicDetailA->course_cohort_coordinator;
        $resolvedB = $academicDetailB->course_cohort_coordinator;

        $this->assertNotNull($resolvedA, 'Student A must resolve to a coordinator');
        $this->assertSame($coordinator2024->id, $resolvedA->id, 'Student A must resolve specifically to the 2024/2025 coordinator');

        $this->assertNotNull($resolvedB, 'Student B must resolve to a coordinator');
        $this->assertSame($coordinator2025->id, $resolvedB->id, 'Student B must resolve specifically to the 2025/2026 coordinator');

        $this->assertNotSame($resolvedA->id, $resolvedB->id, 'Students from different cohorts must not resolve to the same coordinator');
    }

    public function test_cohort_specific_coordinator_resolution_persists_as_students_advance_in_levels(): void
    {
        $department = Department::first() ?? Department::create(['name' => 'Progression Dept ' . uniqid()]);
        $programme = Programme::first() ?? Programme::create(['name' => 'Progression Prog ' . uniqid(), 'abv' => 'PP']);
        $level100 = StudentLevel::where('level', '100')->first() ?? StudentLevel::create(['level' => '100']);
        $level200 = StudentLevel::where('level', '200')->first() ?? StudentLevel::create(['level' => '200']);

        $course = Course::create([
            'name' => 'Advancement Course ' . uniqid(),
            'department_id' => $department->id,
            'programme_id' => $programme->id,
        ]);

        // 100L Entry Coordinator for 2024/2025 cohort
        $coordUser = User::create([
            'email' => 'coord_entry_' . uniqid() . '@example.com',
            'role' => 'coordinator',
            'programme_id' => $programme->id,
            'surname' => 'Entry',
            'firstname' => 'Coordinator',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        $entryCoordinator = \App\Models\Coordinator::create([
            'user_id' => $coordUser->id,
            'course_id' => $course->id,
            'department_id' => $department->id,
            'student_level_id' => $level100->id,
            'academic_session' => '2024/2025',
        ]);

        $student = User::create([
            'email' => 'advancing_student_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'surname' => 'Advancing',
            'firstname' => 'Student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        $academicDetail = AcademicDetail::forceCreate([
            'user_id' => $student->id,
            'matric_no' => '2410909999',
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level100->id,
            'admission_session' => '2024/2025',
        ]);

        // Initially at 100L, resolves to 100L coordinator
        $this->assertSame($entryCoordinator->id, $academicDetail->course_cohort_coordinator?->id);
    }

    private function createAcademicDetail(string $matricNumber): AcademicDetail
    {
        $department = Department::first() ?? Department::create([
            'name' => 'Session Test Department ' . uniqid(),
        ]);
        $programme = Programme::first() ?? Programme::create([
            'name' => 'Session Test Programme ' . uniqid(),
            'abv' => 'ST',
        ]);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);
        $course = Course::create([
            'name' => 'Session Test Course ' . uniqid(),
            'department_id' => $department->id,
            'programme_id' => $programme->id,
        ]);
        $student = User::create([
            'email' => 'session_test_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $programme->id,
            'surname' => 'Session',
            'firstname' => 'Student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        return AcademicDetail::forceCreate([
            'user_id' => $student->id,
            'matric_no' => $matricNumber,
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2026/2027',
        ]);
    }
}
