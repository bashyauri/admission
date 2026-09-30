<?php

namespace Tests\Unit;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\Approval;
use App\Models\Coordinator;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\CourseRegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseRegistrationUnitLimitTest extends TestCase
{
    use RefreshDatabase;

    protected CourseRegistrationService $service;
    protected User $student;
    protected Department $department;
    protected Programme $programme;
    protected AcademicDetail $academicDetail;
    protected StudentLevel $level;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CourseRegistrationService();

        $this->department = Department::first() ?? Department::create(['name' => 'UnitLimitDept']);

        $ugProgramme = Programme::find(ProgrammesEnum::Undergraduate->value);
        if (!$ugProgramme) {
            $ugProgramme = new Programme();
            $ugProgramme->id = ProgrammesEnum::Undergraduate->value;
            $ugProgramme->name = 'Undergraduate';
            $ugProgramme->abv = 'UG';
            $ugProgramme->save();
        }
        $this->programme = $ugProgramme;

        $this->student = new User();
        $this->student->id = (string) \Illuminate\Support\Str::uuid();
        $this->student->programme_id = ProgrammesEnum::Undergraduate->value;
        $this->student->email = 'unitlimit' . rand(1000, 9999) . '@test.com';
        $this->student->password = bcrypt('password');
        $this->student->vpassword = 'password';
        $this->student->role = 'student';
        $this->student->save();

        $this->level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);

        $course = Course::first() ?? Course::create([
            'name' => 'UnitLimitCourse',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $this->academicDetail = AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'MAT/UL/' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level->id,
            'acad_session' => '2024/2025',
        ]);

        $coordinator = Coordinator::create([
            'user_id' => $this->student->id,
            'department_id' => $this->department->id,
        ]);
        Approval::create([
            'academic_detail_id' => $this->academicDetail->id,
            'coordinator_id' => $coordinator->id,
            'pin' => 'PIN-' . rand(1000, 9999),
            'is_used' => true,
            'approval_status' => 'Approved',
        ]);
        \App\Models\Setting::setValue('ACADEMIC_SESSION', '2024/2025');
    }

    /**
     * Test that the service rejects a course registration when it would exceed
     * the configured unit limit for the department and level.
     */
    public function test_service_rejects_registration_exceeding_unit_limit(): void
    {
        DepartmentMaxUnit::updateOrCreate(
            ['department_id' => $this->department->id, 'student_level_id' => $this->level->id],
            ['max_units' => 6]
        );

        // Register 5 units first
        $sc1 = StudentCourse::create([
            'code' => 'TST501', 'title' => 'Existing course',
            'units' => 5, 'student_level_id' => $this->level->id, 'semester' => 1,
        ]);
        $dc1 = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $sc1->id, 'units' => 5,
        ]);
        $this->service->registerCourse($this->academicDetail, $dc1, '2024/2025');

        // Now try to add a 3-unit course → 5 + 3 = 8 > 6 limit
        $sc2 = StudentCourse::create([
            'code' => 'TST502', 'title' => 'Overflow course',
            'units' => 3, 'student_level_id' => $this->level->id, 'semester' => 1,
        ]);
        $dc2 = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $sc2->id, 'units' => 3,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceed the maximum allowed load');

        $this->service->registerCourse($this->academicDetail, $dc2, '2024/2025');
    }

    /**
     * Test that the service allows a course registration when within the limit.
     */
    public function test_service_allows_registration_within_unit_limit(): void
    {
        DepartmentMaxUnit::updateOrCreate(
            ['department_id' => $this->department->id, 'student_level_id' => $this->level->id],
            ['max_units' => 24]
        );

        $sc = StudentCourse::create([
            'code' => 'TST601', 'title' => 'Within limit',
            'units' => 3, 'student_level_id' => $this->level->id, 'semester' => 1,
        ]);
        $dc = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $sc->id, 'units' => 3,
        ]);

        $registration = $this->service->registerCourse($this->academicDetail, $dc, '2024/2025');

        $this->assertNotNull($registration->id);
        $this->assertSame($dc->id, $registration->department_course_id);
        $this->assertSame('TST601', $registration->course_code_snapshot);
    }

    /**
     * Test that a registration proceeds when no DepartmentMaxUnit is configured
     * (falls back to no enforcement when getMaxUnits returns 0).
     */
    public function test_service_allows_registration_when_no_max_configured(): void
    {
        // No DepartmentMaxUnit row → getMaxUnits returns 0 → skip enforcement
        $sc = StudentCourse::create([
            'code' => 'TST701', 'title' => 'No limit configured',
            'units' => 20, 'student_level_id' => $this->level->id, 'semester' => 1,
        ]);
        $dc = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $sc->id, 'units' => 20,
        ]);

        $registration = $this->service->registerCourse($this->academicDetail, $dc, '2024/2025');

        $this->assertNotNull($registration->id);
    }
}
