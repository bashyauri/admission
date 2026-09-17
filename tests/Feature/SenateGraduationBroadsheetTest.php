<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\GraduationEligibility;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\ResultReportingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class SenateGraduationBroadsheetTest extends TestCase
{
    use DatabaseTransactions;

    protected User $examOfficer;
    protected User $graduand1;
    protected User $graduand2;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
    protected string $session = '2025/2026';

    protected function setUp(): void
    {
        parent::setUp();

        $this->programme = Programme::create([
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

        // Graduand 2: Second Class Upper (Direct Entry candidate)
        $this->graduand2 = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'graduand2_' . uniqid() . '@example.com',
            'role' => 'student',
            'password' => bcrypt('password'),
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
            'is_cleared' => false,
            'remarks' => 'Qualified for graduation',
        ]);
    }

    public function test_service_generates_accurate_senate_graduation_broadsheet_data(): void
    {
        $service = app(ResultReportingService::class);

        $data = $service->getSenateGraduationBroadsheet([
            'academic_session' => $this->session,
            'department_id' => $this->department->id,
        ]);

        $this->assertEquals($this->department->id, $data['department']['id']);
        $this->assertEquals($this->session, $data['session']);
        $this->assertCount(2, $data['graduands']);

        $firstGrad = collect($data['graduands'])->firstWhere('user_id', $this->graduand1->id);
        $this->assertNotNull($firstGrad);
        $this->assertEquals('First Class Honours', $firstGrad['class_of_degree']);
        $this->assertEquals(4.65, $firstGrad['final_cgpa']);
        $this->assertTrue($firstGrad['is_cleared']);

        $summary = $data['summary'];
        $this->assertEquals(2, $summary['total_graduands']);
        $this->assertEquals(1, $summary['cleared_count']);
        $this->assertEquals(1, $summary['first_class_count']);
        $this->assertEquals(1, $summary['second_upper_count']);
    }

    public function test_senate_graduation_broadsheet_print_view_renders_successfully(): void
    {
        $this->actingAs($this->examOfficer);

        $sessionParam = str_replace('/', '-', $this->session);
        $response = $this->get(route('exam-officer.senate-graduation-broadsheet', [
            'session' => $sessionParam,
            'department' => $this->department->id,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('reports.senate-graduation-broadsheet');
        $response->assertSee('OFFICIAL SENATE GRADUATION BROADSHEET');
        $response->assertSee('MUKHTAR');
        $response->assertSee('First Class Honours');
        $response->assertSee('SENATE DEGREE CLASSIFICATION DISTRIBUTION SUMMARY');
    }

    public function test_senate_graduation_broadsheet_csv_export_streams_correctly(): void
    {
        $this->actingAs($this->examOfficer);

        $sessionParam = str_replace('/', '-', $this->session);
        $response = $this->get(route('exam-officer.senate-graduation-broadsheet.export', [
            'session' => $sessionParam,
            'department' => $this->department->id,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Senate_Graduation_Broadsheet', (string) $response->headers->get('Content-Disposition'));
    }
}
