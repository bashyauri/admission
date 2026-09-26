<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\AcademicActivity;
use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\StudentStatusRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase67StatusFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $officer;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
    protected StudentLevel $level;
    protected AcademicDetail $academicDetail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'Computer Science Department',
        ]);

        $this->programme = Programme::create([
            'name' => 'B.Sc Computer Science',
            'abv' => 'UG',
        ]);

        $this->course = Course::create([
            'name' => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $this->level = StudentLevel::create([
            'level' => '300',
        ]);

        $this->student = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'status_student@test.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'student',
            'firstname' => 'Status',
            'surname' => 'Student',
        ]);

        $this->officer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'status_officer@test.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'admin',
            'firstname' => 'Senate',
            'surname' => 'Officer',
        ]);

        $this->academicDetail = AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'UG/22/CS/1050',
            'course_id' => $this->course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2022/2023',
        ]);
    }

    public function test_enums_provide_correct_labels_and_utility_methods(): void
    {
        // StudentStatus
        $this->assertEquals('Active', StudentStatus::ACTIVE->label());
        $this->assertEquals('Academic Withdrawal', StudentStatus::ACADEMIC_WITHDRAWAL->label());
        $this->assertTrue(StudentStatus::ACADEMIC_WITHDRAWAL->isWithdrawn());
        $this->assertFalse(StudentStatus::ACTIVE->isWithdrawn());
        $this->assertTrue(StudentStatus::ACTIVE->isActive());
        $this->assertTrue(StudentStatus::REINSTATED->isActive());
        $this->assertTrue(StudentStatus::SUSPENDED->isSuspendedOrExpelled());

        // StudentStatusType
        $this->assertEquals('Academic', StudentStatusType::ACADEMIC->label());
        $this->assertEquals('Disciplinary', StudentStatusType::DISCIPLINARY->label());
        $this->assertContains('academic', StudentStatusType::values());

        // AcademicActivity
        $this->assertEquals('Course Registration', AcademicActivity::COURSE_REGISTRATION->label());
        $this->assertEquals('Graduation', AcademicActivity::GRADUATION->label());
        $this->assertContains('graduation', AcademicActivity::values());
    }

    public function test_can_create_academic_progression_record_and_verify_relations_and_scopes(): void
    {
        $progression = AcademicProgressionRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'semester' => 1,
            'level' => '300',
            'cgpa' => 1.45,
            'standing' => 'PROBATION',
            'withdrawal_recommended' => false,
        ]);

        $recommendation = AcademicProgressionRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'semester' => 2,
            'level' => '300',
            'cgpa' => 0.95,
            'standing' => 'WITHDRAWAL_RECOMMENDED',
            'withdrawal_recommended' => true,
        ]);

        $this->assertDatabaseCount('academic_progression_records', 2);
        $this->assertEquals($this->student->id, $progression->user->id);
        $this->assertEquals($this->academicDetail->id, $progression->academicDetail->id);

        // Test Scopes
        $sessionRecords = AcademicProgressionRecord::forSession('2024/2025')->get();
        $this->assertCount(2, $sessionRecords);

        $semester1Records = AcademicProgressionRecord::forSemester(1)->get();
        $this->assertCount(1, $semester1Records);

        $recommended = AcademicProgressionRecord::withdrawalRecommended()->get();
        $this->assertCount(1, $recommended);
        $this->assertEquals($recommendation->id, $recommended->first()->id);

        // Test User and AcademicDetail relationship
        $this->assertCount(2, $this->student->academicProgressionRecords);
        $this->assertCount(2, $this->academicDetail->academicProgressionRecords);
    }

    public function test_can_create_student_status_record_and_verify_relations_casts_and_scopes(): void
    {
        $statusRecord = StudentStatusRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'reason_code' => 'CGPA_BELOW_MINIMUM',
            'reason' => 'CGPA dropped below mandatory threshold for two consecutive sessions.',
            'academic_session' => '2024/2025',
            'semester' => 2,
            'effective_date' => '2025-06-30',
            'senate_reference' => 'SEN/2025/APP/042',
            'senate_decision_date' => '2025-07-15',
            'senate_decision' => 'APPROVED',
            'reinstatement_eligible' => true,
            'processed_by' => $this->officer->id,
            'notes' => 'Eligible for re-admission upon appeal after 1 academic year.',
        ]);

        $this->assertInstanceOf(StudentStatus::class, $statusRecord->status);
        $this->assertEquals(StudentStatus::ACADEMIC_WITHDRAWAL, $statusRecord->status);
        $this->assertInstanceOf(StudentStatusType::class, $statusRecord->status_type);
        $this->assertEquals(StudentStatusType::ACADEMIC, $statusRecord->status_type);
        $this->assertTrue($statusRecord->reinstatement_eligible);
        $this->assertEquals($this->officer->id, $statusRecord->processedBy->id);

        // Test User & AcademicDetail relationships
        $this->assertCount(1, $this->student->studentStatusRecords);
        $this->assertCount(1, $this->academicDetail->studentStatusRecords);
        $this->assertEquals($statusRecord->id, $this->student->latestStudentStatusRecord->id);
        $this->assertEquals($statusRecord->id, $this->academicDetail->latestStudentStatusRecord->id);

        // Scopes
        $withdrawn = StudentStatusRecord::withdrawn()->get();
        $this->assertCount(1, $withdrawn);

        $active = StudentStatusRecord::active()->get();
        $this->assertCount(0, $active);

        $senateApproved = StudentStatusRecord::senateApproved()->get();
        $this->assertCount(1, $senateApproved);

        $academicType = StudentStatusRecord::byStatusType(StudentStatusType::ACADEMIC)->get();
        $this->assertCount(1, $academicType);

        $forSession = StudentStatusRecord::forSessionAndSemester('2024/2025', 2)->get();
        $this->assertCount(1, $forSession);
    }

    public function test_status_history_and_reinstatement_event_preservation(): void
    {
        // 1. Initial active status
        StudentStatusRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'status' => StudentStatus::ACTIVE,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2023/2024',
            'semester' => 1,
            'effective_date' => '2023-10-01',
        ]);

        // 2. Withdrawal status
        StudentStatusRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'status' => StudentStatus::ACADEMIC_WITHDRAWAL,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2024/2025',
            'semester' => 2,
            'effective_date' => '2025-06-30',
            'senate_decision' => 'APPROVED',
        ]);

        // 3. Reinstatement status
        $reinstatement = StudentStatusRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'status' => StudentStatus::REINSTATED,
            'status_type' => StudentStatusType::ACADEMIC,
            'academic_session' => '2025/2026',
            'semester' => 1,
            'effective_date' => '2025-10-01',
            'senate_decision' => 'APPROVED',
        ]);

        // Ensure all 3 records are preserved in history
        $this->assertCount(3, $this->student->fresh()->studentStatusRecords);

        // Latest status is REINSTATED
        $this->assertEquals(StudentStatus::REINSTATED, $this->student->fresh()->latestStudentStatusRecord->status);
        $this->assertTrue($this->student->fresh()->latestStudentStatusRecord->status->isActive());
    }
}
