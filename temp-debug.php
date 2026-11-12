<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Enums\ProgrammesEnum;
use App\Http\Livewire\Admin\DeMatricNumberReview;
use App\Models\AcademicDetail;
use App\Models\Department;
use App\Models\ProposedCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\DeMatricNumberReviewService;
use Illuminate\Support\Facades\Artisan;

Artisan::call('migrate:fresh', ['--seed' => true]);

$department = Department::create(['name' => 'Computer Science', 'code' => '50']);
$level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);

$firstStudent = User::create([
    'email' => 'de1_' . uniqid() . '@example.com',
    'role' => 'student',
    'programme_id' => ProgrammesEnum::Undergraduate->value,
    'surname' => 'First',
    'firstname' => 'Student',
    'password' => bcrypt('password'),
    'vpassword' => 'password',
]);
ProposedCourse::create(['user_id' => $firstStudent->id,'department_id' => $department->id,'course_id' => 1,'academic_session' => '2026/2027']);
AcademicDetail::create(['user_id' => $firstStudent->id,'department_id' => $department->id,'course_id' => 1,'programme_id' => ProgrammesEnum::Undergraduate->value,'student_level_id' => $level->id,'matric_no' => '2620905001','acad_session' => '2026/2027']);

$secondStudent = User::create([
    'email' => 'de2_' . uniqid() . '@example.com',
    'role' => 'student',
    'programme_id' => ProgrammesEnum::Undergraduate->value,
    'surname' => 'Second',
    'firstname' => 'Student',
    'password' => bcrypt('password'),
    'vpassword' => 'password',
]);
ProposedCourse::create(['user_id' => $secondStudent->id,'department_id' => $department->id,'course_id' => 1,'academic_session' => '2026/2027']);
AcademicDetail::create(['user_id' => $secondStudent->id,'department_id' => $department->id,'course_id' => 1,'programme_id' => ProgrammesEnum::Undergraduate->value,'student_level_id' => $level->id,'matric_no' => '2620905002','acad_session' => '2026/2027']);

$thirdStudent = User::create([
    'email' => 'de3_' . uniqid() . '@example.com',
    'role' => 'student',
    'programme_id' => ProgrammesEnum::Undergraduate->value,
    'surname' => 'Third',
    'firstname' => 'Student',
    'password' => bcrypt('password'),
    'vpassword' => 'password',
]);
ProposedCourse::create(['user_id' => $thirdStudent->id,'department_id' => $department->id,'course_id' => 1,'academic_session' => '2026/2027']);
$detail = AcademicDetail::create(['user_id' => $thirdStudent->id,'department_id' => $department->id,'course_id' => 1,'programme_id' => ProgrammesEnum::Undergraduate->value,'student_level_id' => $level->id,'matric_no' => '2610905003','acad_session' => '2026/2027']);

$service = app(DeMatricNumberReviewService::class);
$bareDetail = AcademicDetail::query()->with(['user', 'department'])->findOrFail($detail->id);
$expected = $service->getSuggestedMatricNumber($bareDetail);
echo 'expected=' . $expected . PHP_EOL;

echo 'beforeUpdate=' . $bareDetail->matric_no . PHP_EOL;
$component = Livewire\Livewire::test(DeMatricNumberReview::class)
    ->set('selectedAcademicDetailIds', [$detail->id])
    ->call('updateSelectedMatricNumbers', $service);

$component->assertSessionHas('success');

echo 'afterUpdate=' . $bareDetail->fresh()->matric_no . PHP_EOL;
