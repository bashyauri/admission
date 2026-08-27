<?php

namespace Tests\Unit;

use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\Course;
use App\Models\CourseChangeHistory;
use App\Models\CourseMapping;
use App\Models\CourseVersion;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\ResultGpaRecord;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Phase1DatabaseFoundationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $student;
    protected User $lecturer;
    protected User $hod;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
    protected StudentCourse $studentCourse;
    protected DepartmentCourse $departmentCourse;
    protected AcademicDetail $academicDetail;
    protected StudentLevel $level;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::first() ?? new Department();
        if (!$this->department->exists) {
            $this->department->name = 'Computer Science Test Dept ' . rand(100, 999);
            $this->department->save();
        }

        $this->programme = Programme::first() ?? new Programme();
        if (!$this->programme->exists) {
            $this->programme->name = 'B.Sc Computer Science ' . rand(100, 999);
            $this->programme->abv = 'UG';
            $this->programme->save();
        }

        $this->student = new User();
        $this->student->id = (string) \Illuminate\Support\Str::uuid();
        $this->student->programme_id = $this->programme->id;
        $this->student->email = 'student' . rand(1000, 9999) . '@test.com';
        $this->student->password = bcrypt('password');
        $this->student->vpassword = 'password';
        $this->student->role = 'student';
        $this->student->firstname = 'Test';
        $this->student->surname = 'Student';
        $this->student->save();

        $this->lecturer = new User();
        $this->lecturer->id = (string) \Illuminate\Support\Str::uuid();
        $this->lecturer->programme_id = $this->programme->id;
        $this->lecturer->email = 'lecturer' . rand(1000, 9999) . '@test.com';
        $this->lecturer->password = bcrypt('password');
        $this->lecturer->vpassword = 'password';
        $this->lecturer->role = 'lecturer';
        $this->lecturer->firstname = 'Test';
        $this->lecturer->surname = 'Lecturer';
        $this->lecturer->save();

        $this->hod = new User();
        $this->hod->id = (string) \Illuminate\Support\Str::uuid();
        $this->hod->programme_id = $this->programme->id;
        $this->hod->email = 'hod' . rand(1000, 9999) . '@test.com';
        $this->hod->password = bcrypt('password');
        $this->hod->vpassword = 'password';
        $this->hod->role = 'hod';
        $this->hod->firstname = 'Test';
        $this->hod->surname = 'HOD';
        $this->hod->save();

        $this->course = new Course();
        $this->course->name = 'Intro to Computer Science';
        $this->course->department_id = $this->department->id;
        $this->course->programme_id = $this->programme->id;
        $this->course->save();

        $this->level = StudentLevel::first() ?? new StudentLevel();
        if (!$this->level->exists) {
            $this->level->level = '100';
            $this->level->save();
        }

        $this->studentCourse = new StudentCourse();
        $this->studentCourse->code = 'CSC101' . rand(10, 99);
        $this->studentCourse->title = 'Intro to Programming ' . rand(10, 99);
        $this->studentCourse->units = '3';
        $this->studentCourse->student_level_id = $this->level->id;
        $this->studentCourse->semester = 1;
        $this->studentCourse->save();

        $this->departmentCourse = new DepartmentCourse();
        $this->departmentCourse->department_id = $this->department->id;
        $this->departmentCourse->student_course_id = $this->studentCourse->id;
        $this->departmentCourse->units = 3;
        $this->departmentCourse->save();

        $this->academicDetail = new AcademicDetail();
        $this->academicDetail->user_id = $this->student->id;
        $this->academicDetail->matric_no = 'MAT/' . rand(1000, 9999);
        $this->academicDetail->course_id = $this->course->id;
        $this->academicDetail->programme_id = $this->programme->id;
        $this->academicDetail->department_id = $this->department->id;
        $this->academicDetail->student_level_id = $this->level->id;
        $this->academicDetail->acad_session = '2024-2025';
        $this->academicDetail->save();
    }

    public function test_can_create_course_version_and_link_to_course(): void
    {
        $version = CourseVersion::create([
            'course_id' => $this->course->id,
            'department_id' => $this->department->id,
            'course_code' => 'CSC101',
            'course_title' => 'Introduction to Computer Programming',
            'credit_units' => 3,
            'semester' => 'first',
            'level' => 100,
            'is_compulsory' => true,
            'academic_session' => '2024-2025',
            'is_active' => true,
            'created_by' => $this->lecturer->id,
            'change_reason' => 'Initial curriculum definition',
        ]);

        $this->assertDatabaseHas('course_versions', [
            'id' => $version->id,
            'course_code' => 'CSC101',
            'academic_session' => '2024-2025',
        ]);

        $this->assertEquals($this->course->id, $version->course->id);
        $this->assertEquals($version->id, $this->course->currentVersion->id);
    }

    public function test_can_create_course_change_history(): void
    {
        $v1 = CourseVersion::create([
            'course_id' => $this->course->id,
            'course_code' => 'CSC101',
            'course_title' => 'Intro to Comp',
            'credit_units' => 2,
            'academic_session' => '2023-2024',
        ]);

        $v2 = CourseVersion::create([
            'course_id' => $this->course->id,
            'course_code' => 'CSC101',
            'course_title' => 'Intro to Computer Programming',
            'credit_units' => 3,
            'academic_session' => '2024-2025',
        ]);

        $history = CourseChangeHistory::create([
            'course_id' => $this->course->id,
            'previous_version_id' => $v1->id,
            'new_version_id' => $v2->id,
            'change_type' => 'unit_change',
            'old_values' => ['credit_units' => 2],
            'new_values' => ['credit_units' => 3],
            'academic_session' => '2024-2025',
            'changed_by' => $this->hod->id,
            'reason' => 'NUC curriculum review updated credit units to 3',
        ]);

        $this->assertDatabaseHas('course_change_history', ['id' => $history->id]);
        $this->assertEquals(2, $history->old_values['credit_units']);
        $this->assertEquals(3, $history->new_values['credit_units']);
    }

    public function test_can_create_course_mapping(): void
    {
        $course2 = new Course();
        $course2->name = 'Advanced Programming';
        $course2->department_id = $this->department->id;
        $course2->programme_id = $this->programme->id;
        $course2->save();

        $mapping = CourseMapping::create([
            'old_course_id' => $this->course->id,
            'new_course_id' => $course2->id,
            'mapping_type' => 'replacement',
            'effective_session' => '2024-2025',
            'created_by' => $this->hod->id,
            'remarks' => 'CSC101 replaces old CMP101',
        ]);

        $this->assertDatabaseHas('course_mappings', ['id' => $mapping->id]);
        $this->assertEquals($this->course->id, $mapping->oldCourse->id);
        $this->assertEquals($course2->id, $mapping->newCourse->id);
    }

    public function test_registered_course_persists_snapshots_and_links_result(): void
    {
        $reg = RegisteredCourse::create([
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2024-2025',
            'course_code_snapshot' => 'CSC101',
            'course_title_snapshot' => 'Intro to Programming',
            'credit_units_snapshot' => 3,
            'semester_snapshot' => 'first',
            'level_snapshot' => 100,
        ]);

        $this->assertDatabaseHas('registered_courses', [
            'id' => $reg->id,
            'course_code_snapshot' => 'CSC101',
            'credit_units_snapshot' => 3,
        ]);

        $result = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'ca_score' => 32.50,
            'exam_score' => 51.00,
            'total_score' => 83.50,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'pending',
            'lecturer_id' => $this->lecturer->id,
        ]);

        $this->assertDatabaseHas('results', [
            'id' => $result->id,
            'grade' => 'A',
            'total_score' => 83.50,
        ]);

        $this->assertEquals($result->id, $reg->result->id);
        $this->assertTrue($this->student->results->contains($result));
    }

    public function test_gpa_record_and_approvals_and_carry_overs(): void
    {
        // 1. GPA Record
        $gpa = ResultGpaRecord::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'semester_gpa' => 4.50,
            'total_credit_units' => 20,
            'total_grade_points' => 90,
            'cumulative_gpa' => 4.50,
            'cumulative_credit_units' => 20,
            'cumulative_grade_points' => 90,
            'class_of_degree' => 'First Class Honours',
        ]);

        $this->assertDatabaseHas('result_gpa_records', ['id' => $gpa->id, 'semester_gpa' => 4.50]);
        $this->assertTrue($this->student->gpaRecords->contains($gpa));

        // 2. Result Approval Record
        $approval = ResultApproval::create([
            'user_id' => $this->student->id,
            'department_id' => $this->department->id,
            'academic_session' => '2024-2025',
            'semester' => 'first',
            'approval_level' => 'hod',
            'approved_by' => $this->hod->id,
            'status' => 'approved',
            'comments' => 'Departmental results verified and approved',
        ]);

        $this->assertDatabaseHas('result_approvals', ['id' => $approval->id, 'approval_level' => 'hod']);

        // 3. Carry Over Record
        $reg = RegisteredCourse::create([
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2023-2024',
        ]);

        $carryOver = CarryOverCourse::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $this->departmentCourse->id,
            'failed_session' => '2023-2024',
            'failed_semester' => 'first',
            'failed_score' => 38.00,
            'failed_grade' => 'F',
            'is_cleared' => false,
        ]);

        $this->assertDatabaseHas('carry_over_courses', ['id' => $carryOver->id, 'failed_grade' => 'F']);
        $this->assertTrue($this->student->carryOverCourses->contains($carryOver));
        $this->assertCount(1, CarryOverCourse::active()->get());
    }
}
