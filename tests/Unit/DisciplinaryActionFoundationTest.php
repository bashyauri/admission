<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\DisciplinaryAction;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\StudentStatusRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DisciplinaryActionFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_disciplinary_action_persists_fields_and_exposes_expected_relationships(): void
    {
        $department = Department::create(['name' => 'Computer Science Department']);
        $programme = Programme::create(['name' => 'B.Sc Computer Science', 'abv' => 'UG']);
        $course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
        ]);
        $level = StudentLevel::create(['level' => '300']);
        $student = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $programme->id,
            'email' => 'disciplinary_student@test.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'student',
            'firstname' => 'Disciplinary',
            'surname' => 'Student',
        ]);
        $officer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $programme->id,
            'email' => 'disciplinary_officer@test.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'exam_officer',
            'firstname' => 'Exam',
            'surname' => 'Officer',
        ]);
        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'matric_no' => 'UG/22/CS/1051',
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2022/2023',
        ]);
        $statusRecord = StudentStatusRecord::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'status' => 'suspended',
            'status_type' => 'disciplinary',
            'academic_session' => '2024/2025',
        ]);

        $action = DisciplinaryAction::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'sanction_type' => 'suspension',
            'course_id' => $course->id,
            'academic_session' => '2024/2025',
            'semester' => 'first',
            'senate_ref_no' => 'SEN-2026-042',
            'verdict_date' => '2026-09-20',
            'effective_session' => '2026/2027',
            'resumption_session' => '2027/2028',
            'is_active' => true,
            'is_appealed' => true,
            'appeal_status' => 'pending',
            'sanctioned_by' => $officer->id,
            'student_status_record_id' => $statusRecord->id,
            'remarks' => 'Senate-approved disciplinary suspension.',
        ]);

        $action->refresh();
        $this->assertDatabaseHas('disciplinary_actions', [
            'id' => $action->id,
            'sanction_type' => 'suspension',
            'senate_ref_no' => 'SEN-2026-042',
            'is_active' => true,
            'is_appealed' => true,
            'student_status_record_id' => $statusRecord->id,
        ]);
        $this->assertTrue($action->is_active);
        $this->assertTrue($action->is_appealed);
        $this->assertSame('2026-09-20', $action->verdict_date->toDateString());
        $this->assertTrue($action->user->is($student));
        $this->assertTrue($action->academicDetail->is($academicDetail));
        $this->assertTrue($action->sanctionedBy->is($officer));
        $this->assertTrue($action->studentStatusRecord->is($statusRecord));
        $this->assertTrue($student->disciplinaryActions->first()->is($action));
        $this->assertTrue($academicDetail->disciplinaryActions->first()->is($action));
    }

    public function test_course_and_status_record_links_are_optional(): void
    {
        $department = Department::create(['name' => 'Law Department']);
        $programme = Programme::create(['name' => 'LL.B Law', 'abv' => 'UG']);
        $course = Course::create([
            'name' => 'Law',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
        ]);
        $level = StudentLevel::create(['level' => '200']);
        $student = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $programme->id,
            'email' => 'disciplinary_optional@test.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'student',
            'firstname' => 'Optional',
            'surname' => 'Links',
        ]);
        $academicDetail = AcademicDetail::create([
            'user_id' => $student->id,
            'matric_no' => 'UG/24/LAW/1052',
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2024/2025',
        ]);

        $action = DisciplinaryAction::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'sanction_type' => 'expulsion',
            'academic_session' => '2024/2025',
            'senate_ref_no' => 'SEN-2026-043',
            'verdict_date' => '2026-09-21',
            'effective_session' => '2026/2027',
        ]);
        $action->refresh();

        $this->assertNull($action->result_id);
        $this->assertNull($action->student_status_record_id);
        $this->assertNull($action->result);
        $this->assertNull($action->studentStatusRecord);
        $this->assertTrue($action->is_active);
        $this->assertFalse($action->is_appealed);
    }
}
