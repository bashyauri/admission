<?php

declare(strict_types=1);

namespace Tests\Unit;

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
use Tests\TestCase;

class Phase6DatabaseFoundationTest extends TestCase
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
            'level' => '400',
        ]);

        $this->student = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'graduand@test.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'student',
            'firstname' => 'Test',
            'surname' => 'Graduand',
            'm_name' => 'Scholar',
        ]);

        $this->officer = User::create([
            'id' => (string) Str::uuid(),
            'programme_id' => $this->programme->id,
            'email' => 'officer@test.com',
            'password' => bcrypt('password'),
            'vpassword' => 'password',
            'role' => 'admin',
            'firstname' => 'Exam',
            'surname' => 'Officer',
        ]);

        $this->academicDetail = AcademicDetail::create([
            'user_id' => $this->student->id,
            'matric_no' => 'UG/20/CS/1001',
            'course_id' => $this->course->id,
            'programme_id' => $this->programme->id,
            'department_id' => $this->department->id,
            'student_level_id' => $this->level->id,
            'acad_session' => '2024/2025',
            'admission_session' => '2020/2021',
        ]);
    }

    public function test_can_create_graduation_eligibility_and_verify_relations_and_scopes(): void
    {
        $eligibility = GraduationEligibility::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'academic_session' => '2024/2025',
            'final_cgpa' => 4.25,
            'class_of_degree' => 'Second Class Honours (Upper Division)',
            'total_units_earned' => 145,
            'total_units_required' => 140,
            'meets_requirements' => true,
            'siwes_completed' => true,
            'general_studies_completed' => true,
            'entrepreneurship_completed' => true,
            'is_cleared' => true,
            'cleared_by' => $this->officer->id,
            'cleared_at' => now(),
            'remarks' => 'Cleared for graduation by Academic Board',
        ]);

        $this->assertDatabaseHas('graduation_eligibilities', [
            'id' => $eligibility->id,
            'user_id' => $this->student->id,
            'academic_session' => '2024/2025',
            'final_cgpa' => 4.25,
            'meets_requirements' => 1,
            'is_cleared' => 1,
        ]);

        // Model relations
        $this->assertEquals($this->student->id, $eligibility->user->id);
        $this->assertEquals($this->student->id, $eligibility->student->id);
        $this->assertEquals($this->academicDetail->id, $eligibility->academicDetail->id);
        $this->assertEquals($this->officer->id, $eligibility->clearedBy->id);

        // Reverse relations
        $this->assertTrue($this->student->graduationEligibilities->contains($eligibility));
        $this->assertEquals($eligibility->id, $this->student->graduationEligibility->id);
        $this->assertTrue($this->academicDetail->graduationEligibilities->contains($eligibility));
        $this->assertEquals($eligibility->id, $this->academicDetail->graduationEligibility->id);

        // Scopes
        $this->assertCount(1, GraduationEligibility::cleared()->get());
        $this->assertCount(1, GraduationEligibility::meetsRequirements()->get());
        $this->assertCount(1, GraduationEligibility::forSession('2024/2025')->get());
        $this->assertCount(0, GraduationEligibility::forSession('2023/2024')->get());
    }

    public function test_can_create_graduation_list_and_items(): void
    {
        $gradList = GraduationList::create([
            'title' => '50th Convocation Graduating List',
            'academic_session' => '2024/2025',
            'ceremony_date' => '2025-11-20',
            'venue' => 'University Convocation Arena',
            'is_published' => true,
            'published_by' => $this->officer->id,
            'published_at' => now(),
        ]);

        $this->assertDatabaseHas('graduation_lists', [
            'id' => $gradList->id,
            'title' => '50th Convocation Graduating List',
            'academic_session' => '2024/2025',
            'is_published' => 1,
        ]);

        $item = GraduationListItem::create([
            'graduation_list_id' => $gradList->id,
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'matric_no' => 'UG/20/CS/1001',
            'full_name' => 'Graduand Scholar Test',
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'final_cgpa' => 4.65,
            'class_of_degree' => 'First Class Honours',
            'rank' => 1,
            'is_present' => true,
        ]);

        $this->assertDatabaseHas('graduation_list_items', [
            'id' => $item->id,
            'graduation_list_id' => $gradList->id,
            'matric_no' => 'UG/20/CS/1001',
            'final_cgpa' => 4.65,
        ]);

        // Model relations
        $this->assertTrue($gradList->items->contains($item));
        $this->assertEquals($this->officer->id, $gradList->publishedBy->id);
        $this->assertEquals($gradList->id, $item->graduationList->id);
        $this->assertEquals($this->student->id, $item->user->id);
        $this->assertEquals($this->student->id, $item->student->id);
        $this->assertEquals($this->academicDetail->id, $item->academicDetail->id);

        // Reverse relations
        $this->assertTrue($this->student->graduationListItems->contains($item));
        $this->assertTrue($this->academicDetail->graduationListItems->contains($item));

        // Scopes
        $this->assertCount(1, GraduationList::published()->get());
        $this->assertCount(1, GraduationList::forSession('2024/2025')->get());
    }

    public function test_can_create_degree_certificate_and_verify_tracking(): void
    {
        $gradList = GraduationList::create([
            'title' => '50th Convocation Graduating List',
            'academic_session' => '2024/2025',
            'is_published' => true,
        ]);

        $certNumber = DegreeCertificate::generateCertificateNumber('2024/2025');
        $this->assertStringStartsWith('CERT-20242025-', $certNumber);

        $certificate = DegreeCertificate::create([
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'graduation_list_id' => $gradList->id,
            'certificate_number' => $certNumber,
            'certificate_type' => 'bachelor',
            'class_of_degree' => 'First Class Honours',
            'issue_date' => '2025-11-20',
            'file_path' => 'certificates/2024_2025/UG20CS1001.pdf',
            'is_printed' => true,
            'is_collected' => true,
            'collected_at' => now(),
            'collected_by' => $this->officer->id,
            'recipient_name' => 'Graduand Scholar Test',
            'remarks' => 'Collected in person with valid national ID',
        ]);

        $this->assertDatabaseHas('degree_certificates', [
            'id' => $certificate->id,
            'certificate_number' => $certNumber,
            'is_collected' => 1,
            'is_printed' => 1,
        ]);

        // Model relations
        $this->assertEquals($this->student->id, $certificate->user->id);
        $this->assertEquals($this->student->id, $certificate->student->id);
        $this->assertEquals($this->academicDetail->id, $certificate->academicDetail->id);
        $this->assertEquals($gradList->id, $certificate->graduationList->id);
        $this->assertEquals($this->officer->id, $certificate->collectedBy->id);

        // Reverse relations
        $this->assertTrue($this->student->degreeCertificates->contains($certificate));
        $this->assertEquals($certificate->id, $this->student->degreeCertificate->id);
        $this->assertTrue($this->academicDetail->degreeCertificates->contains($certificate));
        $this->assertTrue($gradList->certificates->contains($certificate));

        // Scopes
        $this->assertCount(1, DegreeCertificate::collected()->get());
        $this->assertCount(0, DegreeCertificate::pendingCollection()->get());
    }

    public function test_graduation_list_cascades_delete_to_items(): void
    {
        $gradList = GraduationList::create([
            'title' => 'Test Convocation',
            'academic_session' => '2024/2025',
        ]);

        $item = GraduationListItem::create([
            'graduation_list_id' => $gradList->id,
            'user_id' => $this->student->id,
            'academic_detail_id' => $this->academicDetail->id,
            'matric_no' => 'UG/20/CS/1001',
            'full_name' => 'Graduand Scholar Test',
            'programme' => 'B.Sc Computer Science',
            'department' => 'Computer Science',
            'final_cgpa' => 4.65,
            'class_of_degree' => 'First Class Honours',
        ]);

        $this->assertDatabaseHas('graduation_list_items', ['id' => $item->id]);

        $gradList->delete();

        $this->assertDatabaseMissing('graduation_lists', ['id' => $gradList->id]);
        $this->assertDatabaseMissing('graduation_list_items', ['id' => $item->id]);
    }
}
