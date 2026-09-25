<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Http\Livewire\ExamOfficer\ManageCertificates;
use App\Http\Livewire\Student\MyResults;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\DegreeCertificate;
use App\Models\Department;
use App\Models\GraduationEligibility;
use App\Models\GraduationList;
use App\Models\GraduationListItem;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $examOfficer;
    protected User $student;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
    protected AcademicDetail $academicDetail;
    protected GraduationList $graduationList;
    protected GraduationListItem $graduationListItem;
    protected string $session = '2025/2026';

    protected function setUp(): void
    {
        parent::setUp();

        $this->programme = Programme::forceCreate([
            'id' => ProgrammesEnum::Undergraduate->value,
            'name' => 'B.Sc Computer Science ' . uniqid(),
            'abv' => 'UG',
        ]);

        $this->department = Department::create([
            'name' => 'Computer Science Dept ' . uniqid(),
            'faculty' => 'Faculty of Science',
        ]);

        $this->course = Course::create([
            'name' => 'B.Sc Computer Science',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $level400 = StudentLevel::where('level', '400')->first() ?? StudentLevel::create(['level' => '400']);

        // Exam Officer
        $this->examOfficer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'examofficer_' . uniqid() . '@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Officer',
            'surname' => 'Exam',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        // Student
        $this->student = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'student_' . uniqid() . '@example.com',
            'role' => 'student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Aliyu',
            'surname' => 'Suleiman',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $this->academicDetail = AcademicDetail::create([
            'user_id' => $this->student->id,
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
            'course_id' => $this->course->id,
            'student_level_id' => $level400->id,
            'matric_no' => 'MAT-CERT-' . rand(1000, 9999),
            'acad_session' => $this->session,
            'admission_session' => '2021/2022',
        ]);

        $this->graduationList = GraduationList::create([
            'title' => '40th Convocation Graduands',
            'academic_session' => $this->session,
            'is_published' => true,
        ]);

        $this->graduationListItem = GraduationListItem::create([
            'graduation_list_id' => $this->graduationList->id,
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'matric_no' => $this->academicDetail->matric_no,
            'full_name' => 'Aliyu Suleiman',
            'programme' => 'B.Sc Computer Science',
            'department' => $this->department->name,
            'final_cgpa' => 4.45,
            'class_of_degree' => 'Second Class Upper Division',
            'rank' => 1,
            'is_present' => false,
        ]);
    }

    /**
     * Test exam officer can view certificate management page.
     */
    public function test_exam_officer_can_view_certificate_management_page(): void
    {
        $response = $this->actingAs($this->examOfficer)->get(route('exam-officer.certificates'));
        $response->assertStatus(200);
        $response->assertSee('Degree Certificate Generation & Collection Tracking');
    }

    /**
     * Test unauthorized student cannot access certificate management.
     */
    public function test_unauthorized_student_cannot_access_certificate_management(): void
    {
        $response = $this->actingAs($this->student)->get(route('exam-officer.certificates'));
        $response->assertRedirect(route('student.dashboard'));
    }

    /**
     * Test exam officer can issue a certificate to a staged graduand.
     */
    public function test_exam_officer_can_issue_single_certificate(): void
    {
        $this->actingAs($this->examOfficer);

        Livewire::test(ManageCertificates::class)
            ->set('selectedSession', $this->session)
            ->call('openIssueModal', $this->graduationListItem->id)
            ->assertSet('showIssueModal', true)
            ->assertSet('issueMatricNo', $this->academicDetail->matric_no)
            ->call('confirmIssueCertificate')
            ->assertSet('showIssueModal', false);

        $this->assertDatabaseHas('degree_certificates', [
            'user_id' => $this->student->id,
            'graduation_list_id' => $this->graduationList->id,
            'academic_detail_id' => $this->academicDetail->id,
            'class_of_degree' => 'Second Class Upper Division',
            'is_collected' => false,
        ]);

        $cert = DegreeCertificate::where('user_id', $this->student->id)->first();
        $this->assertNotNull($cert);
        $this->assertStringStartsWith('CERT-', $cert->certificate_number);
    }

    /**
     * Test exam officer can batch issue certificates for unissued graduands.
     */
    public function test_exam_officer_can_batch_issue_certificates(): void
    {
        // Add a second graduand
        $student2 = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'student2_' . uniqid() . '@example.com',
            'role' => 'student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Fatima',
            'surname' => 'Garba',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $acad2 = AcademicDetail::create([
            'user_id' => $student2->id,
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
            'course_id' => $this->course->id,
            'student_level_id' => $this->academicDetail->student_level_id,
            'matric_no' => 'MAT-CERT-2-' . rand(1000, 9999),
            'acad_session' => $this->session,
            'admission_session' => '2021/2022',
        ]);

        GraduationListItem::create([
            'graduation_list_id' => $this->graduationList->id,
            'user_id' => $student2->id,
            'academic_detail_id' => $acad2->id,
            'matric_no' => $acad2->matric_no,
            'full_name' => 'Fatima Garba',
            'programme' => 'B.Sc Computer Science',
            'department' => $this->department->name,
            'final_cgpa' => 4.75,
            'class_of_degree' => 'First Class Honours',
            'rank' => 2,
            'is_present' => false,
        ]);

        $this->actingAs($this->examOfficer);

        Livewire::test(ManageCertificates::class)
            ->set('selectedSession', $this->session)
            ->call('batchIssueCertificates');

        $this->assertDatabaseHas('degree_certificates', [
            'user_id' => $this->student->id,
        ]);

        $this->assertDatabaseHas('degree_certificates', [
            'user_id' => $student2->id,
            'class_of_degree' => 'First Class Honours',
        ]);

        $this->assertEquals(2, DegreeCertificate::where('graduation_list_id', $this->graduationList->id)->count());
    }

    /**
     * Test toggling print status of a certificate.
     */
    public function test_exam_officer_can_toggle_certificate_printed_status(): void
    {
        $cert = DegreeCertificate::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'graduation_list_id' => $this->graduationList->id,
            'certificate_number' => 'CERT-20252026-TEST01',
            'certificate_type' => 'bachelor',
            'class_of_degree' => 'Second Class Upper Division',
            'issue_date' => now()->toDateString(),
            'is_printed' => false,
            'is_collected' => false,
        ]);

        $this->actingAs($this->examOfficer);

        Livewire::test(ManageCertificates::class)
            ->call('togglePrinted', $cert->id);

        $this->assertTrue($cert->fresh()->is_printed);

        // Toggle back
        Livewire::test(ManageCertificates::class)
            ->call('togglePrinted', $cert->id);

        $this->assertFalse($cert->fresh()->is_printed);
    }

    /**
     * Test exam officer can log collection of a degree certificate.
     */
    public function test_exam_officer_can_log_certificate_collection(): void
    {
        $cert = DegreeCertificate::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'graduation_list_id' => $this->graduationList->id,
            'certificate_number' => 'CERT-20252026-TEST02',
            'certificate_type' => 'bachelor',
            'class_of_degree' => 'Second Class Upper Division',
            'issue_date' => now()->toDateString(),
            'is_printed' => false,
            'is_collected' => false,
        ]);

        $this->actingAs($this->examOfficer);

        Livewire::test(ManageCertificates::class)
            ->call('openCollectModal', $cert->id)
            ->assertSet('showCollectModal', true)
            ->set('collectRecipientName', 'Aliyu Suleiman (Self)')
            ->set('collectRemarks', 'National ID Card verified: NIN 12345678901')
            ->call('confirmLogCollection')
            ->assertSet('showCollectModal', false);

        $fresh = $cert->fresh();
        $this->assertTrue($fresh->is_collected);
        $this->assertEquals('Aliyu Suleiman (Self)', $fresh->recipient_name);
        $this->assertEquals($this->examOfficer->id, $fresh->collected_by);
        $this->assertNotNull($fresh->collected_at);
        $this->assertTrue($fresh->is_printed);
    }

    /**
     * Test student portal displays graduation clearance & certificate readiness badge.
     */
    public function test_student_portal_displays_graduation_clearance_and_certificate_badge(): void
    {
        GraduationEligibility::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => $this->session,
            'final_cgpa' => 4.45,
            'class_of_degree' => 'Second Class Upper Division',
            'total_units_earned' => 130,
            'total_units_required' => 120,
            'meets_requirements' => true,
            'siwes_completed' => true,
            'general_studies_completed' => true,
            'entrepreneurship_completed' => true,
            'is_cleared' => true,
            'cleared_by' => $this->examOfficer->id,
            'cleared_at' => now(),
            'remarks' => 'Cleared for degree conferment',
        ]);

        $cert = DegreeCertificate::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'graduation_list_id' => $this->graduationList->id,
            'certificate_number' => 'CERT-20252026-STU001',
            'certificate_type' => 'bachelor',
            'class_of_degree' => 'Second Class Upper Division',
            'issue_date' => now()->toDateString(),
            'is_printed' => true,
            'is_collected' => false,
        ]);

        $this->actingAs($this->student);

        Livewire::test(MyResults::class)
            ->assertSee('Graduation & Degree Status')
            ->assertSee('CERT-20252026-STU001')
            ->assertSee('Certificate Ready for Collection');
    }
}
