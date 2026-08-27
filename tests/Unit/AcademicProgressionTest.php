<?php

namespace Tests\Unit;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\ResultGpaRecord;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\AcademicProgressionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AcademicProgressionTest extends TestCase
{
    use DatabaseTransactions;

    protected AcademicProgressionService $progressionService;
    protected Department $department;
    protected Programme $programme;
    protected StudentLevel $level100;
    protected StudentLevel $level200;
    protected StudentLevel $level400;

    protected function setUp(): void
    {
        parent::setUp();
        $this->progressionService = new AcademicProgressionService();

        $this->department = Department::first() ?? new Department();
        if (!$this->department->exists) {
            $this->department->name = 'CS Dept ' . rand(100, 999);
            $this->department->save();
        }

        $this->programme = Programme::first() ?? new Programme();
        if (!$this->programme->exists) {
            $this->programme->name = 'B.Sc CS';
            $this->programme->abv = 'CS';
            $this->programme->save();
        }

        $this->level100 = StudentLevel::where('level', '100')->first() ?? StudentLevel::create(['level' => '100']);
        $this->level200 = StudentLevel::where('level', '200')->first() ?? StudentLevel::create(['level' => '200']);
        $this->level400 = StudentLevel::where('level', '400')->first() ?? StudentLevel::create(['level' => '400']);
    }

    public function test_fresh_utme_student_starts_at_level_1(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'fresh' . rand(1000, 9999) . '@test.com';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        $level = $this->progressionService->getNextEligibleLevel($user);
        $this->assertEquals(1, $level);
    }

    public function test_student_with_good_standing_is_promoted(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'good' . rand(1000, 9999) . '@test.com';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        $course = Course::first() ?? Course::create([
            'name' => 'CS101',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level100->id, // 100L (id 1)
            'acad_session' => '2023-2024',
        ]);

        ResultGpaRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2023-2024',
            'semester' => 'second',
            'semester_gpa' => 3.50,
            'cumulative_gpa' => 3.50,
            'total_credit_units' => 20,
            'total_grade_points' => 70,
            'cumulative_credit_units' => 40,
            'cumulative_grade_points' => 140,
            'class_of_degree' => 'Second Class Upper Division',
        ]);

        $standing = $this->progressionService->determineAcademicStanding($user);
        $this->assertEquals(AcademicProgressionService::STANDING_PROMOTED, $standing['standing']);

        $nextLevel = $this->progressionService->getNextEligibleLevel($user);
        $this->assertEquals(2, $nextLevel); // Promoted to 200L
    }

    public function test_student_with_poor_standing_repeats_level(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'repeat' . rand(1000, 9999) . '@test.com';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        $course = Course::first() ?? Course::create([
            'name' => 'CS101',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level100->id, // 100L
            'acad_session' => '2023-2024',
        ]);

        ResultGpaRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2023-2024',
            'semester' => 'second',
            'semester_gpa' => 0.85,
            'cumulative_gpa' => 0.85, // Poor standing (< 1.00)
            'total_credit_units' => 20,
            'total_grade_points' => 17,
            'cumulative_credit_units' => 40,
            'cumulative_grade_points' => 34,
            'class_of_degree' => 'Fail',
        ]);

        $standing = $this->progressionService->determineAcademicStanding($user);
        $this->assertEquals(AcademicProgressionService::STANDING_REPEAT, $standing['standing']);

        $nextLevel = $this->progressionService->getNextEligibleLevel($user);
        $this->assertEquals(1, $nextLevel); // Stays at 100L to repeat
    }

    public function test_final_year_spillover_student_capped_at_max_level(): void
    {
        $user = new User();
        $user->id = (string) \Illuminate\Support\Str::uuid();
        $user->programme_id = ProgrammesEnum::Undergraduate->value;
        $user->email = 'spill' . rand(1000, 9999) . '@test.com';
        $user->password = bcrypt('password');
        $user->vpassword = 'password';
        $user->role = 'student';
        $user->save();

        $course = new Course();
        $course->name = 'CS401';
        $course->department_id = $this->department->id;
        $course->programme_id = $this->programme->id;
        $course->semesters = '8'; // 4-year programme (8 semesters)
        $course->save();

        $academicDetail = AcademicDetail::create([
            'user_id' => $user->id,
            'matric_no' => 'MAT/' . rand(1000, 9999),
            'course_id' => $course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level400->id, // 400L in a 4-year degree
            'acad_session' => '2023-2024',
        ]);

        // Student has an active uncleared carry-over
        $studentCourse = StudentCourse::first() ?? StudentCourse::create([
            'code' => 'CSC101' . rand(10, 99),
            'title' => 'Intro Prog ' . rand(10, 99),
            'units' => '3',
            'student_level_id' => $this->level100->id,
            'semester' => 1,
        ]);

        $deptCourse = DepartmentCourse::first() ?? DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $studentCourse->id,
            'units' => 3,
        ]);

        $reg = RegisteredCourse::create([
            'department_course_id' => $deptCourse->id,
            'academic_detail_id' => $academicDetail->id,
            'student_level_id' => 4,
            'units' => '3',
            'academic_session' => '2023-2024',
        ]);

        CarryOverCourse::create([
            'user_id' => $user->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $deptCourse->id,
            'failed_session' => '2023-2024',
            'failed_semester' => 'first',
            'failed_score' => 30,
            'failed_grade' => 'F',
            'is_cleared' => false,
        ]);

        ResultGpaRecord::create([
            'user_id' => $user->id,
            'academic_detail_id' => $academicDetail->id,
            'academic_session' => '2023-2024',
            'semester' => 'second',
            'semester_gpa' => 2.50,
            'cumulative_gpa' => 2.50,
            'total_credit_units' => 20,
            'total_grade_points' => 50,
            'cumulative_credit_units' => 140,
            'cumulative_grade_points' => 350,
            'class_of_degree' => 'Second Class Lower Division',
        ]);

        $standing = $this->progressionService->determineAcademicStanding($user);
        $this->assertEquals(AcademicProgressionService::STANDING_SPILLOVER, $standing['standing']);

        // Should be capped at 4 (400 Level), never overflowing to 500 Level
        $nextLevel = $this->progressionService->getNextEligibleLevel($user);
        $this->assertEquals(4, $nextLevel);
    }
}
