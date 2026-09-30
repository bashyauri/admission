<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\AcademicActivity;
use App\Enums\ProgrammesEnum;
use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Models\Programme;
use App\Models\StudentLevel;
use App\Models\StudentStatusAudit;
use App\Models\StudentTransaction;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\StudentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentStatusLifecycleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private StudentStatusService $statusService;
    private User $admin;
    private User $student;
    private StudentLevel $studentLevel;

    protected function setUp(): void
    {
        parent::setUp();

        $programme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate([
                'id' => ProgrammesEnum::Undergraduate->value,
                'name' => 'Undergraduate',
                'abv' => 'UG',
            ]);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
        $this->student = User::factory()->create([
            'role' => 'student',
            'programme_id' => $programme->id,
        ]);
        $this->studentLevel = StudentLevel::first() ?? StudentLevel::create(['level' => '100']);
        $this->statusService = app(StudentStatusService::class);
    }

    public function test_senate_approved_voluntary_withdrawal_changes_activity_and_writes_audit(): void
    {
        $record = $this->statusService->processVoluntaryWithdrawal(
            user: $this->student,
            reason: 'Student requested voluntary withdrawal',
            academicSession: '2025/2026',
            effectiveDate: '2026-02-10',
            senateReference: 'SEN-2026-310',
        );

        $this->assertSame(StudentStatus::VOLUNTARY_WITHDRAWAL, $record->status);
        $this->assertSame(StudentStatusType::VOLUNTARY, $record->status_type);
        $this->assertSame($record->id, $this->statusService->getCurrentStatus($this->student)->id);
        $this->assertFalse($this->statusService->canPerformAcademicActivity($this->student, AcademicActivity::COURSE_REGISTRATION));
        $this->assertDatabaseHas('student_status_audits', [
            'student_id' => $this->student->id,
            'actor_id' => $this->admin->id,
            'student_status_record_id' => $record->id,
            'action' => 'VOLUNTARY_WITHDRAWAL_PROCESSED',
            'senate_reference' => 'SEN-2026-310',
        ]);
    }

    public function test_senate_approved_medical_withdrawal_changes_activity_and_writes_audit(): void
    {
        $record = $this->statusService->processMedicalWithdrawal(
            user: $this->student,
            reason: 'Medical board recommended leave',
            academicSession: '2025/2026',
            effectiveDate: '2026-02-12',
            senateReference: 'SEN/2026/311',
        );

        $this->assertSame(StudentStatus::MEDICAL_WITHDRAWAL, $record->status);
        $this->assertSame(StudentStatusType::MEDICAL, $record->status_type);
        $this->assertSame($record->id, $this->statusService->getCurrentStatus($this->student)->id);
        $this->assertFalse($this->statusService->canPerformAcademicActivity($this->student, AcademicActivity::SCHOOL_FEES));
        $this->assertDatabaseHas('student_status_audits', [
            'student_id' => $this->student->id,
            'actor_id' => $this->admin->id,
            'student_status_record_id' => $record->id,
            'action' => 'MEDICAL_WITHDRAWAL_PROCESSED',
            'senate_reference' => 'SEN/2026/311',
        ]);
    }

    public function test_existing_pending_payment_remains_available_in_history_after_withdrawal(): void
    {
        $pendingPayment = StudentTransaction::create([
            'transaction_id' => 'PENDING-BEFORE-WITHDRAWAL-001',
            'user_id' => $this->student->id,
            'student_levels_id' => $this->studentLevel->id,
            'date' => '2026-02-01',
            'status' => '025',
            'amount' => 50000,
            'resource' => 'Undergraduate School Fees',
            'RRR' => 'PENDING-RRR-001',
            'acad_session' => '2025/2026',
        ]);

        $this->statusService->processVoluntaryWithdrawal(
            user: $this->student,
            reason: 'Student requested voluntary withdrawal',
            academicSession: '2025/2026',
            effectiveDate: '2026-02-10',
            senateReference: 'SEN-2026-312',
        );

        $this->assertSame('025', $pendingPayment->fresh()->status);
        $this->assertSame($pendingPayment->id, app(PaymentService::class)->getStudentInvoice(
            $this->student->id,
            'Undergraduate School Fees',
        )?->id);
        $this->assertSame($pendingPayment->id, app(PaymentService::class)->getStudentPayments($this->student->id)->first()?->id);
        $this->assertSame(1, StudentStatusAudit::where('student_id', $this->student->id)->count());
    }
}
