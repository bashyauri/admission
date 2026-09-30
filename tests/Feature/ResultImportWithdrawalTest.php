<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\CourseAllocation;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\StudentStatusRecord;
use App\Models\User;
use App\Imports\ResultImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ResultImportWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_skips_withdrawn_student_with_clear_warning_and_preserves_existing_result(): void
    {
        $programme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate([
                'id' => ProgrammesEnum::Undergraduate->value,
                'name' => 'Undergraduate',
                'abv' => 'UG',
            ]);
        $department = Department::create(['name' => 'Import Status Department']);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '200']);
        $studentCourse = StudentCourse::create([
            'code' => 'STATUS101',
            'title' => 'Status Import Test',
            'units' => '3',
            'student_level_id' => $level->id,
            'semester' => 1,
        ]);
        $departmentCourse = DepartmentCourse::create([
            'student_course_id' => $studentCourse->id,
            'department_id' => $department->id,
            'units' => 3,
        ]);
        $lecturer = User::factory()->create(['role' => 'lecturer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'programme_id' => $programme->id,
        ]);
        $detail = AcademicDetail::create([
            'user_id' => $student->id,
            'matric_no' => 'UG/IMPORT/STATUS-001',
            'course_id' => Course::query()->create([
                'name' => 'Import Status Course',
                'department_id' => $department->id,
                'programme_id' => $programme->id,
                'semesters' => 8,
            ])->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'acad_session' => '2025/2026',
            'admission_session' => '2024/2025',
        ]);
        $registeredCourse = RegisteredCourse::create([
            'academic_detail_id' => $detail->id,
            'department_course_id' => $departmentCourse->id,
            'student_level_id' => $level->id,
            'units' => '3',
            'academic_session' => '2025/2026',
        ]);
        $allocation = CourseAllocation::create([
            'department_course_id' => $departmentCourse->id,
            'department_id' => $department->id,
            'lecturer_id' => $lecturer->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
        ]);
        $existingResult = Result::create([
            'user_id' => $student->id,
            'registered_course_id' => $registeredCourse->id,
            'department_course_id' => $departmentCourse->id,
            'academic_detail_id' => $detail->id,
            'academic_session' => '2025/2026',
            'semester' => 'first',
            'ca_score' => 20,
            'exam_score' => 40,
            'total_score' => 60,
            'grade' => 'C',
            'grade_point' => 3,
            'credit_units' => 3,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);
        StudentStatusRecord::create([
            'user_id' => $student->id,
            'academic_detail_id' => $detail->id,
            'status' => 'voluntary_withdrawal',
            'status_type' => 'voluntary',
            'reason' => 'Approved withdrawal',
            'academic_session' => '2025/2026',
            'effective_date' => '2025-09-01',
            'senate_reference' => 'SEN-2026-902',
            'senate_decision_date' => '2025-08-25',
            'senate_decision' => 'SENATE_APPROVED',
            'processed_by' => $admin->id,
            'reinstatement_eligible' => true,
        ]);

        $import = new ResultImport($allocation, '2025/2026', 'first');
        $import->collection(new Collection([new Collection([
            'matric_no' => 'UG/IMPORT/STATUS-001',
            'ca_score' => '35',
            'exam_score' => '55',
        ])]));

        $this->assertSame(0, $import->successCount);
        $this->assertFalse($import->previewRows[0]['is_valid']);
        $this->assertStringContainsString('Skipped: Voluntary Withdrawal status prevents new result entry', $import->previewRows[0]['message']);
        $this->assertSame($existingResult->id, $existingResult->fresh()->id);
        $this->assertSame('60.00', $existingResult->fresh()->total_score);
        $this->assertSame(1, Result::where('user_id', $student->id)->count());
    }
}
