<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ProgrammesEnum;
use App\Enums\Role;
use App\Models\AcademicDetail;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\Transcript;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranscriptTest extends TestCase
{
    use RefreshDatabase;

    protected User $ugStudent;
    protected User $pgStudent;
    protected Department $department;
    protected Programme $ugProgramme;
    protected Programme $pgProgramme;
    protected StudentLevel $level100;
    protected AcademicDetail $academicDetail;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::first() ?? Department::create(['name' => 'Department of Physics']);

        $this->ugProgramme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate([
                'id'   => ProgrammesEnum::Undergraduate->value,
                'name' => 'Undergraduate',
                'abv'  => 'UG',
            ]);

        $this->pgProgramme = Programme::find(ProgrammesEnum::PG->value)
            ?? Programme::forceCreate([
                'id'   => ProgrammesEnum::PG->value,
                'name' => 'Postgraduate',
                'abv'  => 'PG',
            ]);

        $this->level100 = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);

        $this->ugStudent = User::create([
            'email'             => 'ug_feat_' . uniqid() . '@example.com',
            'role'              => Role::STUDENT->value,
            'programme_id'      => $this->ugProgramme->id,
            'surname'           => 'Garba',
            'firstname'         => 'Zainab',
            'password'          => bcrypt('password'),
            'vpassword'         => 'password',
            'email_verified_at' => now(),
        ]);

        $this->pgStudent = User::create([
            'email'             => 'pg_feat_' . uniqid() . '@example.com',
            'role'              => Role::STUDENT->value,
            'programme_id'      => $this->pgProgramme->id,
            'surname'           => 'Yakubu',
            'firstname'         => 'Kabir',
            'password'          => bcrypt('password'),
            'vpassword'         => 'password',
            'email_verified_at' => now(),
        ]);

        $this->course = Course::create([
            'name'          => 'Physics with Electronics',
            'department_id' => $this->department->id,
            'programme_id'  => $this->ugProgramme->id,
        ]);

        $this->academicDetail = AcademicDetail::forceCreate([
            'user_id'           => $this->ugStudent->id,
            'matric_no'         => 'UG/2023/PHY/2042',
            'course_id'         => $this->course->id,
            'programme_id'      => $this->ugProgramme->id,
            'department_id'     => $this->department->id,
            'student_level_id'  => $this->level100->id,
            'admission_session' => '2023/2024',
        ]);

        $sc = StudentCourse::create([
            'code'             => 'PHY101',
            'title'            => 'General Physics I',
            'units'            => 3,
            'semester'         => 1,
            'student_level_id' => $this->level100->id,
        ]);
        $dc = DepartmentCourse::create([
            'student_course_id' => $sc->id,
            'department_id'     => $this->department->id,
            'units'             => 3,
        ]);
        $rc = RegisteredCourse::create([
            'academic_detail_id'   => $this->academicDetail->id,
            'department_course_id' => $dc->id,
            'student_level_id'     => $this->level100->id,
            'units'                => 3,
            'academic_session'     => '2023/2024',
        ]);
        Result::forceCreate([
            'user_id'               => $this->ugStudent->id,
            'registered_course_id'  => $rc->id,
            'department_course_id'  => $dc->id,
            'academic_detail_id'    => $this->academicDetail->id,
            'course_code_snapshot'  => 'PHY101',
            'course_title_snapshot' => 'General Physics I',
            'credit_units_snapshot' => 3,
            'semester'              => 'first',
            'academic_session'      => '2023/2024',
            'ca_score'              => 35,
            'exam_score'            => 45,
            'total_score'           => 80,
            'grade'                 => 'A',
            'grade_point'           => 5,
            'status'                => 'released',
        ]);
    }

    public function test_guest_is_redirected_from_transcript_download(): void
    {
        $response = $this->get(route('student.transcript'));
        $response->assertRedirect();
    }

    public function test_postgraduate_student_cannot_download_undergraduate_transcript(): void
    {
        $this->actingAs($this->pgStudent);

        $response = $this->get(route('student.transcript'));
        $response->assertForbidden();
    }

    public function test_undergraduate_student_can_download_transcript_pdf(): void
    {
        $this->actingAs($this->ugStudent);

        $response = $this->get(route('student.transcript'));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('transcript_UG_2023_PHY_2042.pdf', (string) $response->headers->get('content-disposition'));
    }

    public function test_undergraduate_student_can_preview_transcript(): void
    {
        $this->actingAs($this->ugStudent);

        $response = $this->get(route('student.transcript.preview'));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('inline;', (string) $response->headers->get('content-disposition'));
    }

    public function test_public_transcript_verification_with_valid_code(): void
    {
        $transcript = Transcript::create([
            'user_id'           => $this->ugStudent->id,
            'request_number'    => 'TRQ-20260909-TEST',
            'verification_code' => 'TRV-TEST-VERI-1234',
            'destination'       => 'Public Verification',
            'status'            => 'ready',
            'fee_paid'          => true,
        ]);

        $response = $this->get(route('transcripts.verify', ['code' => 'TRV-TEST-VERI-1234']));

        $response->assertOk();
        $response->assertSee('Authentic Academic Transcript Verified', false);
        $response->assertSee('GARBA', false);
        $response->assertSee('Zainab', false);
        $response->assertSee('UG/2023/PHY/2042', false);
        $response->assertSee('TRV-TEST-VERI-1234', false);
        $response->assertSee('5.00 / 5.00', false);
        $response->assertSee('First Class Honours', false);
    }

    public function test_public_transcript_verification_with_invalid_code_renders_not_found(): void
    {
        $response = $this->get(route('transcripts.verify', ['code' => 'TRV-INVALID-CODE-9999']));

        $response->assertOk();
        $response->assertSee('Unrecognized Verification Code', false);
        $response->assertSee('TRV-INVALID-CODE-9999', false);
    }
}
