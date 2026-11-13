<?php

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Http\Livewire\Admin\DeMatricNumberReview;
use App\Models\AcademicDetail;
use App\Models\Department;
use App\Models\ProposedCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\DeMatricNumberReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class DeMatricReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_review_and_update_invalid_de_matric_numbers(): void
    {
        Config::set('remita.settings.admin_academic_session', '2026/2027');
        $department = Department::create([
            'name' => 'Computer Science',
            'code' => '50',
        ]);
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
        ProposedCourse::create([
            'user_id' => $firstStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2026/2027',
        ]);
        AcademicDetail::create([
            'user_id' => $firstStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $level->id,
            'matric_no' => '2620905001',
            'acad_session' => '2026/2027',
        ]);

        $secondStudent = User::create([
            'email' => 'de2_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'surname' => 'Second',
            'firstname' => 'Student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        ProposedCourse::create([
            'user_id' => $secondStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2026/2027',
        ]);
        AcademicDetail::create([
            'user_id' => $secondStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $level->id,
            'matric_no' => '2620905002',
            'acad_session' => '2026/2027',
        ]);

        $utmeStudent = User::create([
            'email' => 'utme_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'jamb_no' => 'UTME' . uniqid(),
            'surname' => 'UTME',
            'firstname' => 'Student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        ProposedCourse::create([
            'user_id' => $utmeStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2026/2027',
        ]);
        $utmeDetail = AcademicDetail::create([
            'user_id' => $utmeStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $level->id,
            'matric_no' => '26209050003',
            'acad_session' => '2026/2027',
        ]);

        $thirdStudent = User::create([
            'email' => 'de3_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'surname' => 'Third',
            'firstname' => 'Student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        ProposedCourse::create([
            'user_id' => $thirdStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2026/2027',
        ]);
        $detail = AcademicDetail::create([
            'user_id' => $thirdStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $level->id,
            'matric_no' => '2610905004',
            'acad_session' => '2026/2027',
        ]);

        $reviewService = app(DeMatricNumberReviewService::class);

        $this->assertSame('26209050004', $reviewService->getSuggestedMatricNumber($detail));
        $this->assertStringStartsWith('2620', $reviewService->getSuggestedMatricNumber($detail));

        Livewire::test(DeMatricNumberReview::class)
            ->set('selectedAcademicDetailIds', [$detail->id])
            ->call('updateSelectedMatricNumbers');

        $this->assertSame('26209050004', $detail->fresh()->matric_no);
        $this->assertNull($reviewService->getSuggestedMatricNumber($utmeDetail));
        $this->assertFalse($reviewService->updateMatricNumber($utmeDetail, '2026/2027'));
    }

    public function test_review_only_lists_and_updates_records_from_the_current_session(): void
    {
        Config::set('remita.settings.admin_academic_session', '2026/2027');
        $department = Department::create(['name' => 'Session Scope', 'code' => '51']);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);
        $student = User::create([
            'email' => 'old_de_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'surname' => 'Historical',
            'firstname' => 'Student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        ProposedCourse::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2026/2027',
        ]);
        $detail = AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $level->id,
            'matric_no' => '2410905101',
            'acad_session' => '2026/2027',
            'admission_session' => '2024/2025',
        ]);

        $reviewService = app(DeMatricNumberReviewService::class);

        Livewire::test(DeMatricNumberReview::class)
            ->assertDontSee('2410905101')
            ->assertDontSee('26209051001');

        $this->assertFalse($reviewService->updateMatricNumber($detail, '2026/2027'));
        $this->assertSame('2410905101', $detail->fresh()->matric_no);
    }

    public function test_malformed_short_matric_is_corrected_using_positional_serial(): void
    {
        // Regression: a bug caused the first DE student's serial to be unpadded ("1" instead of "001"),
        // producing an 8-character matric like "26109061". The service must NOT reuse the last 3
        // characters of such a malformed (non-10-digit) matric as the serial; it must use
        // the student's positional index among their cohort instead.
        Config::set('remita.settings.admin_academic_session', '2026/2027');
        $department = Department::create(['name' => 'Malformed Matric Test', 'code' => '55']);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);

        $deStudent = User::create([
            'email' => 'malformed_de_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'surname' => 'Malformed',
            'firstname' => 'Matric',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        ProposedCourse::create([
            'user_id' => $deStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2026/2027',
        ]);
        // Simulate the broken 8-character matric produced by the old unpadded-serial bug
        $brokenDetail = AcademicDetail::create([
            'user_id' => $deStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $level->id,
            'matric_no' => '261090551',   // 9 chars — malformed, last 3 chars are "551"
            'acad_session' => '2026/2027',
            'admission_session' => '2026/2027',
        ]);

        $reviewService = app(DeMatricNumberReviewService::class);
        $suggested = $reviewService->getSuggestedMatricNumber($brokenDetail);

        // Should use positional serial (1st student = 001), NOT "551" from the malformed tail
        // Department code "55" is 2 digits → matric = 26+20+90+55+001 = 11 chars (correct for this dept)
        $this->assertSame('26209055001', $suggested);
        $this->assertSame(11, strlen($suggested));
        $this->assertStringStartsWith('2620', $suggested);
    }

    public function test_first_de_matric_number_uses_three_digit_serial(): void
    {
        $department = Department::create([
            'name' => 'Matric Zero Padding',
            'code' => '50',
        ]);
        $student = User::create([
            'email' => 'first_de_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'surname' => 'First',
            'firstname' => 'DE',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        ProposedCourse::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2024/2025',
        ]);
        $detail = AcademicDetail::create([
            'user_id' => $student->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => (StudentLevel::first() ?? StudentLevel::create(['level' => '100']))->id,
            'matric_no' => '2620905001',
            'acad_session' => '2026/2027',
        ]);

        $this->assertSame(
            '26209050001',
            app(DeMatricNumberReviewService::class)->getSuggestedMatricNumber($detail),
        );
    }

    public function test_postgraduate_students_are_excluded_from_de_matric_review(): void
    {
        $department = Department::create([
            'name' => 'Computer Science',
            'code' => '50',
        ]);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);

        $undergraduateStudent = User::create([
            'email' => 'ug_de_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'surname' => 'Undergrad',
            'firstname' => 'Student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        ProposedCourse::create([
            'user_id' => $undergraduateStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2026/2027',
        ]);
        AcademicDetail::create([
            'user_id' => $undergraduateStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::Undergraduate->value,
            'student_level_id' => $level->id,
            'matric_no' => '2620905004',
            'acad_session' => '2026/2027',
        ]);

        $postgraduateStudent = User::create([
            'email' => 'pg_de_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => ProgrammesEnum::PG->value,
            'surname' => 'Postgrad',
            'firstname' => 'Student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);
        ProposedCourse::create([
            'user_id' => $postgraduateStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'academic_session' => '2026/2027',
        ]);
        AcademicDetail::create([
            'user_id' => $postgraduateStudent->id,
            'department_id' => $department->id,
            'course_id' => 1,
            'programme_id' => ProgrammesEnum::PG->value,
            'student_level_id' => $level->id,
            'matric_no' => '2020905009',
            'acad_session' => '2026/2027',
        ]);

        $invalidRecords = app(DeMatricNumberReviewService::class)->getInvalidDeRecords();

        $this->assertCount(1, $invalidRecords);
        $this->assertSame($undergraduateStudent->id, $invalidRecords->first()->user_id);
        $this->assertNotSame($postgraduateStudent->id, $invalidRecords->first()->user_id);
    }
}
