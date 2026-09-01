<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Livewire\Coordinator\CoordinatorResultReview;
use App\Http\Livewire\ExamOfficer\ExamOfficerResultReview;
use App\Models\AcademicDetail;
use App\Models\Coordinator;
use App\Models\Course;
use App\Models\CourseAllocation;
use App\Models\Department;
use App\Models\DepartmentCourse;
use App\Models\HodUser;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\ResultApproval;
use App\Models\ResultGpaRecord;
use App\Models\StudentCourse;
use App\Models\StudentLevel;
use App\Models\User;
use App\Models\UserCapability;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ResultApprovalWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $student;
    protected User $lecturer;
    protected User $coordinator;
    protected User $hod;
    protected User $examOfficer;
    protected Department $department;
    protected Programme $programme;
    protected Course $course;
    protected StudentCourse $studentCourse;
    protected DepartmentCourse $departmentCourse;
    protected AcademicDetail $academicDetail;
    protected StudentLevel $level;
    protected CourseAllocation $allocation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::first() ?? Department::create(['name' => 'Computer Science ' . rand(100, 999)]);
        $this->programme = Programme::first() ?? Programme::create(['name' => 'Undergraduate ' . rand(100, 999), 'abv' => 'UG']);

        $this->level = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);

        // Student
        $this->student = User::create([
            'email' => 'student_wf_' . uniqid() . '@example.com',
            'role' => 'student',
            'programme_id' => $this->programme->id,
            'surname' => 'Audu',
            'firstname' => 'Musa',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        $this->course = Course::create([
            'name' => 'CSC101',
            'department_id' => $this->department->id,
            'programme_id' => $this->programme->id,
        ]);

        $this->academicDetail = AcademicDetail::forceCreate([
            'user_id' => $this->student->id,
            'matric_no' => 'MAT/' . rand(10000, 99999),
            'course_id' => $this->course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level->id,
        ]);

        // Lecturer
        $this->lecturer = User::create([
            'email' => 'lecturer_wf_' . uniqid() . '@example.com',
            'role' => 'lecturer',
            'programme_id' => $this->programme->id,
            'surname' => 'Dr. Bello',
            'firstname' => 'Ibrahim',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        $this->coordinator = User::create([
            'email' => 'coordinator_wf_' . uniqid() . '@example.com',
            'role' => 'coordinator',
            'programme_id' => $this->programme->id,
            'surname' => 'Dr. Okafor',
            'firstname' => 'Ada',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        Coordinator::create([
            'user_id' => $this->coordinator->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level->id,
            'academic_session' => '2024-2025',
        ]);

        // HOD
        $this->hod = User::create([
            'email' => 'hod_wf_' . uniqid() . '@example.com',
            'role' => 'hod',
            'programme_id' => $this->programme->id,
            'surname' => 'Prof. Aminu',
            'firstname' => 'Garba',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        HodUser::create([
            'user_id' => $this->hod->id,
            'department_id' => $this->department->id,
        ]);

        // Exam Officer
        $this->examOfficer = User::create([
            'email' => 'examofficer_wf_' . uniqid() . '@example.com',
            'role' => 'exam_officer',
            'programme_id' => $this->programme->id,
            'surname' => 'Mrs. Dayo',
            'firstname' => 'Grace',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
        ]);

        $this->studentCourse = StudentCourse::create([
            'code' => 'CSC101_' . rand(100, 999),
            'title' => 'Introduction to Computing ' . rand(1000, 99999),
            'units' => '3',
            'student_level_id' => $this->level->id,
            'semester' => 1,
        ]);

        $this->departmentCourse = DepartmentCourse::create([
            'department_id' => $this->department->id,
            'student_course_id' => $this->studentCourse->id,
            'units' => 3,
        ]);

        $this->allocation = CourseAllocation::create([
            'department_course_id' => $this->departmentCourse->id,
            'lecturer_id' => $this->lecturer->id,
            'academic_session' => '2024-2025',
            'semester' => 'first',
            'allocated_by' => $this->hod->id,
        ]);
    }

    public function test_coordinator_can_review_and_approve_submitted_results(): void
    {
        $reg = RegisteredCourse::create([
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2024-2025',
        ]);

        $result = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'ca_score' => 30.0,
            'exam_score' => 45.0,
            'total_score' => 75.0,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'submitted',
            'lecturer_id' => $this->lecturer->id,
            'coordinator_id' => $this->coordinator->coordinator->id,
        ]);

        $this->actingAs($this->coordinator);

        Livewire::test(CoordinatorResultReview::class)
            ->set('selectedSession', '2024-2025')
            ->set('selectedSemester', 'first')
            ->call('selectCourse', $this->departmentCourse->id)
            ->assertSee('CSC101')
            ->assertSee('Audu Musa')
            ->call('approveStudentResults')
            ->assertHasNoErrors();

        $result->refresh();
        $this->assertEquals('exam_officer_approved', $result->status);
        $this->assertEquals($this->coordinator->id, $result->coordinator_approved_by);
        $this->assertNotNull($result->coordinator_approved_at);

        // Verify audit record created
        $approval = ResultApproval::where('department_id', $this->department->id)
            ->where('approval_level', 'coordinator')
            ->where('status', 'approved')
            ->first();

        $this->assertNotNull($approval);
        $this->assertEquals($this->coordinator->id, $approval->approved_by);
    }

    public function test_coordinator_can_return_results_to_lecturer(): void
    {
        $reg = RegisteredCourse::create([
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2024-2025',
        ]);

        $result = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'ca_score' => 20.0,
            'exam_score' => 30.0,
            'total_score' => 50.0,
            'grade' => 'C',
            'grade_point' => 3,
            'credit_units' => 3,
            'status' => 'submitted',
            'lecturer_id' => $this->lecturer->id,
            'coordinator_id' => $this->coordinator->coordinator->id,
        ]);

        $this->actingAs($this->coordinator);

        Livewire::test(CoordinatorResultReview::class)
            ->set('selectedSession', '2024-2025')
            ->set('selectedSemester', 'first')
            ->call('selectCourse', $this->departmentCourse->id)
            ->set('rejectionReason', 'CA scores require recalculation for Musa.')
            ->call('rejectStudentResults')
            ->assertHasNoErrors();

        $result->refresh();
        $this->assertEquals('pending', $result->status);
        $this->assertEquals('CA scores require recalculation for Musa.', $result->remarks);

        // Verify audit log
        $approval = ResultApproval::where('department_id', $this->department->id)
            ->where('approval_level', 'coordinator')
            ->where('status', 'rejected')
            ->first();

        $this->assertNotNull($approval);
        $this->assertEquals('CA scores require recalculation for Musa.', $approval->comments);
    }

    public function test_exam_officer_can_release_results_and_trigger_gpa_calculation(): void
    {
        $reg = RegisteredCourse::create([
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2024-2025',
        ]);

        $result = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'ca_score' => 35.0,
            'exam_score' => 50.0,
            'total_score' => 85.0,
            'grade' => 'A',
            'grade_point' => 5,
            'credit_units' => 3,
            'grade_point_total' => 15,
            'status' => 'exam_officer_approved',
            'lecturer_id' => $this->lecturer->id,
            'coordinator_id' => $this->coordinator->coordinator->id,
            'coordinator_approved_by' => $this->coordinator->id,
            'coordinator_approved_at' => now(),
        ]);

        $this->actingAs($this->examOfficer);

        Livewire::test(ExamOfficerResultReview::class)
            ->set('selectedSession', '2024-2025')
            ->set('selectedSemester', 'first')
            ->set('selectedDepartmentId', $this->department->id)
            ->call('inspectCourse', $this->departmentCourse->id)
            ->assertSee('CSC101')
            ->call('releaseCourseResults')
            ->assertHasNoErrors();

        $result->refresh();
        $this->assertEquals('released', $result->status);
        $this->assertEquals($this->examOfficer->id, $result->exam_officer_approved_by);
        $this->assertNotNull($result->exam_officer_approved_at);

        // Verify ResultGpaRecord was automatically computed and saved
        $gpaRecord = ResultGpaRecord::where('user_id', $this->student->id)
            ->where('academic_session', '2024-2025')
            ->where('semester', 'first')
            ->first();

        $this->assertNotNull($gpaRecord);
        $this->assertEquals(5.00, (float) $gpaRecord->semester_gpa);
        $this->assertEquals(3, $gpaRecord->total_credit_units);
        $this->assertEquals('First Class Honours', $gpaRecord->class_of_degree);

        // Verify audit log
        $approval = ResultApproval::where('department_id', $this->department->id)
            ->where('approval_level', 'exam_officer')
            ->where('status', 'released')
            ->first();

        $this->assertNotNull($approval);
    }

    public function test_exam_officer_can_return_results_to_coordinator(): void
    {
        $reg = RegisteredCourse::create([
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'student_level_id' => $this->level->id,
            'units' => '3',
            'academic_session' => '2024-2025',
        ]);

        $result = Result::create([
            'user_id' => $this->student->id,
            'registered_course_id' => $reg->id,
            'department_course_id' => $this->departmentCourse->id,
            'academic_detail_id' => $this->academicDetail->id,
            'semester' => 'first',
            'academic_session' => '2024-2025',
            'ca_score' => 25.0,
            'exam_score' => 35.0,
            'total_score' => 60.0,
            'grade' => 'B',
            'grade_point' => 4,
            'credit_units' => 3,
            'status' => 'exam_officer_approved',
            'coordinator_id' => $this->coordinator->coordinator->id,
            'coordinator_approved_by' => $this->coordinator->id,
            'coordinator_approved_at' => now(),
        ]);

        $this->actingAs($this->examOfficer);

        Livewire::test(ExamOfficerResultReview::class)
            ->set('selectedSession', '2024-2025')
            ->set('selectedSemester', 'first')
            ->set('selectedDepartmentId', $this->department->id)
            ->call('inspectCourse', $this->departmentCourse->id)
            ->set('rejectionReason', 'Discrepancy in continuous assessment format.')
            ->call('rejectCourseResults')
            ->assertHasNoErrors();

        $result->refresh();
        $this->assertEquals('submitted', $result->status);
        $this->assertStringContainsString('Discrepancy in continuous assessment format.', $result->remarks);
    }
}
