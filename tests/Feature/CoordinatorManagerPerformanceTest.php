<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Livewire\Admin\CoordinatorManager;
use App\Models\AcademicDetail;
use App\Models\Coordinator;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CoordinatorManagerPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_render_uses_grouped_student_counts_instead_of_n_plus_one_queries(): void
    {
        $programme = Programme::create([
            'name' => 'Undergraduate Test Programme',
            'abv' => 'UG',
        ]);

        $admin = User::create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'programme_id' => $programme->id,
            'surname' => 'Admin',
            'firstname' => 'Test',
            'email' => 'admin_' . uniqid() . '@example.com',
            'role' => 'admin',
            'password' => bcrypt('secret'),
            'vpassword' => 'secret',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $level = StudentLevel::firstOrCreate(['level' => '300']);

        for ($i = 1; $i <= 30; $i++) {
            $department = Department::create(['name' => 'Computer Science ' . $i]);

            $course = Course::create([
                'name' => 'Course ' . $i,
                'programme_id' => $programme->id,
                'department_id' => $department->id,
                'code' => 'CS' . $i,
            ]);

            $coordinatorUser = User::create([
                'id' => '22222222-2222-2222-2222-' . str_pad((string) $i, 12, '0', STR_PAD_LEFT),
                'programme_id' => $programme->id,
                'surname' => 'Coordinator',
                'firstname' => 'User ' . $i,
                'email' => 'coord_' . $i . '_' . uniqid() . '@example.com',
                'role' => 'coordinator',
                'password' => bcrypt('secret'),
                'vpassword' => 'secret',
                'email_verified_at' => now(),
            ]);

            Coordinator::create([
                'user_id' => $coordinatorUser->id,
                'course_id' => $course->id,
                'department_id' => $department->id,
                'student_level_id' => $level->id,
                'academic_session' => '2025/2026',
            ]);

            for ($j = 1; $j <= 3; $j++) {
                AcademicDetail::create([
                    'user_id' => $coordinatorUser->id,
                    'programme_id' => $programme->id,
                    'course_id' => $course->id,
                    'department_id' => $department->id,
                    'student_level_id' => $level->id,
                    'admission_session' => '2025/2026',
                    'matric_no' => 'UG/2025/' . str_pad((string) ($i * 100 + $j), 6, '0', STR_PAD_LEFT),
                ]);
            }
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($admin)
            ->test(CoordinatorManager::class)
            ->assertOk();

        $academicDetailQueryCount = collect(DB::getQueryLog())
            ->filter(fn (array $query) => str_contains(strtolower($query['query']), 'from "academic_details"')
                || str_contains(strtolower($query['query']), 'from `academic_details`')
                || str_contains(strtolower($query['query']), 'from academic_details'))
            ->count();

        $this->assertLessThan(3, $academicDetailQueryCount, 'CoordinatorManager still triggers N+1 student count queries for each row.');
    }
}
