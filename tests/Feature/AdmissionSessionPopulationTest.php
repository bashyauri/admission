<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\AdmissionSessionSynchronizer;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdmissionSessionPopulationTest extends TestCase
{
    use DatabaseTransactions;

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
