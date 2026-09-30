<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Enums\Role;
use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Models\AcademicDetail;
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
use App\Services\ResultReportingService;
use App\Services\TranscriptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranscriptWithdrawalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_senate_approved_withdrawal_is_annotated_and_reported_while_history_remains(): void
    {
        $programme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate(['id' => ProgrammesEnum::Undergraduate->value, 'name' => 'Undergraduate', 'abv' => 'UG']);
        $department = Department::create(['name' => 'Computer Science']);
        $course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $department->id,
            'programme_id' => $programme->id,
            'semesters' => 8,
        ]);
        $level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);
        $student = User::create([
            'email' => 'withdrawal-transcript-' . uniqid() . '@example.test',
            'role' => Role::STUDENT->value,
            'programme_id' => $programme->id,
            'surname' => 'Bello',
            'firstname' => 'Umar',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
        ]);
        $academicDetail = AcademicDetail::forceCreate([
            'user_id' => $student->id,
            'matric_no' => 'UG/WITHDRAWAL/' . strtoupper(substr(sha1(uniqid('', true)), 0, 8)),
            'course_id' => $course->id,
            'programme_id' => $programme->id,
            'department_id' => $department->id,
            'student_level_id' => $level->id,
            'admission_session' => '2023/2024',
        ]);

        $studentCourse = StudentCourse::create([
            'code' => 'CSC101',
            'title' => 'Introduction to Computing',
            'units' => 3,
            'semester' => 1,
            'student_level_id' => $level->id,
        ]);
        $departmentCourse = DepartmentCourse::create([
            'student_course_id' => $studentCourse->id,
            'department_id' => $department->id,
            'units' => 3,
        ]);
        $registeredCourse = RegisteredCourse::create([
            'academic_detail_id' => $academicDetail->id,
            'department_course_id' => $departmentCourse->id,
            'student_level_id' => $level->id,
            'units' => 3,
            'academic_session' => '2023/2024',
        ]);
        Result::forceCreate([
            'user_id' => $student->id,
            'registered_course_id' => $registeredCourse->id,
            'department_course_id' => $departmentCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'course_code_snapshot' => 'CSC101',
            'course_title_snapshot' => 'Introduction to Computing',
            'credit_units_snapshot' => 3,
            'semester' => 'first',
            'academic_session' => '2023/2024',
            'total_score' => 25,
            'grade' => 'F',
            'grade_point' => 0,
            'status' => 'released',
        ]);

        $reporting = app(ResultReportingService::class);
        $filters = [
            'department_id' => $department->id,
            'academic_session' => '2023/2024',
            'semester' => 'first',
            'student_level_id' => $level->id,
            'admission_session' => '2023/2024',
        ];

        $beforeDecision = $reporting->getDepartmentalBroadsheet($filters);
        $beforeRow = collect($beforeDecision['students'])->firstWhere('user_id', $student->id);
        $this->assertNull($beforeRow['status_text']);

        $transcripts = app(TranscriptService::class);
        $beforeTranscript = $transcripts->buildTranscriptData($student);
        $this->assertNull($beforeTranscript['withdrawalAnnotation']);
        $this->assertCount(1, $beforeTranscript['resultsBreakdown']);

        StudentStatusRecord::create([
            'user_id' => $student->id,
            'academic_detail_id' => $academicDetail->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'reason_code' => 'SENATE_DECISION',
            'reason' => 'Academic withdrawal approved by Senate.',
            'academic_session' => '2023/2024',
            'semester' => 1,
            'effective_date' => '2024-02-01',
            'senate_reference' => 'SEN-2024-101',
            'senate_decision' => 'SENATE_APPROVED',
            'senate_decision_date' => '2024-02-01',
        ]);

        $afterDecision = $reporting->getDepartmentalBroadsheet($filters);
        $afterRow = collect($afterDecision['students'])->firstWhere('user_id', $student->id);
        $this->assertSame('WITHDRAWN: ACADEMIC WITHDRAWAL', $afterRow['status_text']);
        $this->assertSame('WITHDRAWN FROM PROGRAMME', $afterRow['status_display']);
        $this->assertSame('2023/2024', $afterRow['status_session']);
        $this->assertSame('2024-02-01', $afterRow['status_effective_date']);
        $this->assertSame('SEN-2024-101', $afterRow['status_senate_reference']);
        $this->assertSame(1, $afterDecision['summary']['withdrawn_count']);

        $afterTranscript = $transcripts->buildTranscriptData($student);
        $this->assertSame('Academic Withdrawal', $afterTranscript['withdrawalAnnotation']['status']);
        $this->assertTrue($afterTranscript['withdrawalAnnotation']['is_current']);
        $this->assertSame('2023/2024', $afterTranscript['withdrawalAnnotation']['academic_session']);
        $this->assertSame('SEN-2024-101', $afterTranscript['withdrawalAnnotation']['senate_reference']);
        $this->assertSame(0.0, $afterTranscript['withdrawalAnnotation']['cgpa']);
        $this->assertCount(1, $afterTranscript['resultsBreakdown']);

        $transcriptHtml = view('transcripts.official', $afterTranscript)->render();
        $this->assertStringContainsString('Official Withdrawal', $transcriptHtml);
        $this->assertStringContainsString('CGPA at withdrawal: 0.00', $transcriptHtml);
        $this->assertStringContainsString('CSC101', $transcriptHtml);
    }
}
