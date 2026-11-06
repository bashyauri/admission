<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Http\Livewire\Transactions\UtmeSchoolFeesInvoice;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\FeeStructure;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class UtmeSchoolFeesInvoiceLevelTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_displays_correct_level_name_when_level_id_differs_from_ordinal(): void
    {
        $department = Department::create(['name' => 'Microbiology']);
        $programme = Programme::create([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Microbiology',
            'abv' => 'MCB',
        ]);

        // Reproduce real database: id 1 = 100, id 2 = 200, id 3 = Spill Over, id 6 = 300
        $level100 = StudentLevel::create(['id' => 1, 'level' => '100']);
        $level200 = StudentLevel::create(['id' => 2, 'level' => '200']);
        $levelSpill = StudentLevel::create(['id' => 3, 'level' => 'Spill Over']);
        $level400 = StudentLevel::create(['id' => 4, 'level' => '400']);
        $level500 = StudentLevel::create(['id' => 5, 'level' => '500']);
        $level300 = StudentLevel::create(['id' => 6, 'level' => '300']);

        $course = Course::create([
            'name' => 'B.Sc. Microbiology',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semesters' => 8,
        ]);

        $fee = new FeeStructure();
        $fee->department_id = $department->id;
        $fee->programme_id = $programme->id;
        $fee->student_level_id = $level300->id; // id 6
        $fee->fee_amount = 70730.00;
        $fee->is_fresh_fee = false;
        $fee->save();

        $user = new User();
        $user->id = (string) Str::uuid();
        $user->programme_id = $programme->id;
        $user->email = 'khadija@test.com';
        $user->phone = '07044786341';
        $user->surname = 'Ibrahim';
        $user->firstname = 'Khadija';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        // Student is currently 200 level (id = 2), progressing to 300 level (id = 6)
        AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => '2410906054',
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level200->id,
            'admission_session' => '2024/2025',
            'acad_session' => '2025/2026',
        ]);

        $user = $user->fresh();
        $this->actingAs($user);

        Livewire::test(UtmeSchoolFeesInvoice::class, ['user' => $user])
            ->assertSet('currentLevel', 6)
            ->assertSet('currentLevelName', '300 Level')
            ->assertSee('Payment for 300 Level')
            ->assertDontSee('Payment for 600 Level');
    }
}
