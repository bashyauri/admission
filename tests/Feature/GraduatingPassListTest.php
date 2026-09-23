<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\GraduationEligibility;
use App\Models\GraduationList;
use App\Models\GraduationListItem;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\ResultReportingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GraduatingPassListTest extends TestCase
{
    use RefreshDatabase;

    protected User $examOfficer;
    protected User $graduand1;
    protected User $graduand2;
    protected User $graduand3;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
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

        // Graduand 1: First Class Cleared
        $this->graduand1 = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'graduand1_' . uniqid() . '@example.com',
            'role' => 'student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Mansur',
            'surname' => 'Mukhtar',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $acad1 = AcademicDetail::create([
            'user_id' => $this->graduand1->id,
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
            'course_id' => $this->course->id,
            'student_level_id' => $level400->id,
            'matric_no' => 'MAT-' . rand(1000, 9999),
            'acad_session' => $this->session,
            'admission_session' => '2021/2022',
        ]);

        GraduationEligibility::create([
            'user_id' => $this->graduand1->id,
            'academic_detail_id' => $acad1->id,
            'academic_session' => $this->session,
            'final_cgpa' => 4.65,
            'class_of_degree' => 'First Class Honours',
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

        // Graduand 2: Second Class Upper (Cleared)
        $this->graduand2 = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'graduand2_' . uniqid() . '@example.com',
            'role' => 'student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Abubakar',
            'surname' => 'Abdullahi',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $acad2 = AcademicDetail::create([
            'user_id' => $this->graduand2->id,
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
            'course_id' => $this->course->id,
            'student_level_id' => $level400->id,
            'matric_no' => 'MAT-' . rand(1000, 9999),
            'acad_session' => $this->session,
            'admission_session' => '2022/2023',
        ]);

        GraduationEligibility::create([
            'user_id' => $this->graduand2->id,
            'academic_detail_id' => $acad2->id,
            'academic_session' => $this->session,
            'final_cgpa' => 3.80,
            'class_of_degree' => 'Second Class Upper Division',
            'total_units_earned' => 95,
            'total_units_required' => 90,
            'meets_requirements' => true,
            'siwes_completed' => true,
            'general_studies_completed' => true,
            'entrepreneurship_completed' => true,
            'is_cleared' => true,
            'cleared_by' => $this->examOfficer->id,
            'cleared_at' => now(),
            'remarks' => 'Qualified for graduation',
        ]);

        // Graduand 3: Second Class Lower (Qualified but not cleared)
        $this->graduand3 = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'graduand3_' . uniqid() . '@example.com',
            'role' => 'student',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'email_verified_at' => now(),
            'firstname' => 'Fatima',
            'surname' => 'Aliyu',
            'phone' => '080' . rand(10000000, 99999999),
        ]);

        $acad3 = AcademicDetail::create([
            'user_id' => $this->graduand3->id,
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
            'course_id' => $this->course->id,
            'student_level_id' => $level400->id,
            'matric_no' => 'MAT-' . rand(1000, 9999),
            'acad_session' => $this->session,
            'admission_session' => '2021/2022',
        ]);

        GraduationEligibility::create([
            'user_id' => $this->graduand3->id,
            'academic_detail_id' => $acad3->id,
            'academic_session' => $this->session,
            'final_cgpa' => 3.20,
            'class_of_degree' => 'Second Class Lower Division',
            'total_units_earned' => 120,
            'total_units_required' => 120,
            'meets_requirements' => true,
            'siwes_completed' => true,
            'general_studies_completed' => true,
            'entrepreneurship_completed' => true,
            'is_cleared' => false,
            'remarks' => 'Qualified awaiting clearance',
        ]);
    }

    public function test_service_generates_accurate_senate_pass_list_data(): void
    {
        $service = app(ResultReportingService::class);

        $data = $service->getSenatePassList([
            'academic_session' => $this->session,
            'department_id' => $this->department->id,
            'cleared_only' => false,
        ]);

        $this->assertEquals($this->department->id, $data['department']['id']);
        $this->assertEquals($this->session, $data['session']);
        $this->assertCount(3, $data['graduands']);

        // Check grouping by class of degree
        $this->assertArrayHasKey('First Class Honours', $data['grouped_by_class']);
        $this->assertArrayHasKey('Second Class Upper Division', $data['grouped_by_class']);
        $this->assertArrayHasKey('Second Class Lower Division', $data['grouped_by_class']);

        $this->assertCount(1, $data['grouped_by_class']['First Class Honours']);
        $this->assertCount(1, $data['grouped_by_class']['Second Class Upper Division']);
        $this->assertCount(1, $data['grouped_by_class']['Second Class Lower Division']);

        // Verify cleared_only filters correctly
        $clearedData = $service->getSenatePassList([
            'academic_session' => $this->session,
            'department_id' => $this->department->id,
            'cleared_only' => true,
        ]);

        $this->assertCount(2, $clearedData['graduands']);
        $this->assertCount(0, $clearedData['grouped_by_class']['Second Class Lower Division']);
    }

    public function test_senate_pass_list_view_renders_successfully(): void
    {
        $this->actingAs($this->examOfficer);

        $sessionParam = str_replace('/', '-', $this->session);
        $response = $this->get(route('exam-officer.senate-pass-list', [
            'session' => $sessionParam,
            'department' => $this->department->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('reports.senate-pass-list');
        $response->assertSee('OFFICIAL SENATE PASS LIST');
        $response->assertSee('Mukhtar');
        $response->assertSee('First Class Honours');
        $response->assertSee('Second Class Upper Division');
        $response->assertSee('SENATE PASS LIST SUMMARY STATISTICS');
    }

    public function test_senate_pass_list_csv_export_streams_correctly(): void
    {
        $this->actingAs($this->examOfficer);

        $sessionParam = str_replace('/', '-', $this->session);
        $response = $this->get(route('exam-officer.senate-pass-list.export', [
            'session' => $sessionParam,
            'department' => $this->department->id,
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Senate_Pass_List', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_pass_list_with_graduation_list_items(): void
    {
        // Create a fresh exam officer for this test
        $examOfficer = User::create([
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

        // Update graduation eligibility with new officer
        GraduationEligibility::where('user_id', $this->graduand1->id)
            ->update(['cleared_by' => $examOfficer->id]);
        GraduationEligibility::where('user_id', $this->graduand2->id)
            ->update(['cleared_by' => $examOfficer->id]);

        // Create a graduation list and stage some students
        $graduationList = GraduationList::create([
            'academic_session' => $this->session,
            'department_id' => $this->department->id,
            'title' => '2025/2026 Graduation List',
        ]);

        // Stage graduand1 and graduand2 (cleared students)
        GraduationListItem::create([
            'graduation_list_id' => $graduationList->id,
            'user_id' => $this->graduand1->id,
            'academic_detail_id' => $this->graduand1->academicDetail->id,
            'matric_no' => $this->graduand1->academicDetail->matric_no,
            'full_name' => trim($this->graduand1->surname . ' ' . $this->graduand1->firstname . ' ' . $this->graduand1->m_name),
            'programme' => $this->programme->name,
            'department' => $this->department->name,
            'final_cgpa' => 4.65,
            'class_of_degree' => 'First Class Honours',
        ]);

        GraduationListItem::create([
            'graduation_list_id' => $graduationList->id,
            'user_id' => $this->graduand2->id,
            'academic_detail_id' => $this->graduand2->academicDetail->id,
            'matric_no' => $this->graduand2->academicDetail->matric_no,
            'full_name' => trim($this->graduand2->surname . ' ' . $this->graduand2->firstname . ' ' . $this->graduand2->m_name),
            'programme' => $this->programme->name,
            'department' => $this->department->name,
            'final_cgpa' => 3.80,
            'class_of_degree' => 'Second Class Upper Division',
        ]);

        $service = app(ResultReportingService::class);

        $data = $service->getSenatePassList([
            'academic_session' => $this->session,
            'department_id' => $this->department->id,
            'cleared_only' => true,
        ]);

        // Should return staged students from graduation list
        $this->assertCount(2, $data['graduands']);
        $this->assertEquals('Officially Staged for Degree Conferment', $data['graduands'][0]['remarks']);
    }

    public function test_pass_list_summary_statistics(): void
    {
        $service = app(ResultReportingService::class);

        $data = $service->getSenatePassList([
            'academic_session' => $this->session,
            'department_id' => $this->department->id,
            'cleared_only' => false,
        ]);

        $summary = $data['summary'];

        $this->assertEquals(3, $summary['total_graduands']);
        $this->assertEquals(1, $summary['first_class_count']);
        $this->assertEquals(1, $summary['second_upper_count']);
        $this->assertEquals(1, $summary['second_lower_count']);
        $this->assertEquals(33.3, $summary['first_class_percentage']);
        $this->assertEquals(33.3, $summary['second_upper_percentage']);
        $this->assertEquals(33.3, $summary['second_lower_percentage']);
    }

    public function test_unauthorized_user_cannot_access_pass_list(): void
    {
        $response = $this->actingAs($this->graduand1)->get(route('exam-officer.senate-pass-list', [
            'session' => str_replace('/', '-', $this->session),
            'department' => $this->department->id,
        ]));

        $response->assertRedirect(route('student.dashboard'));
    }
}