<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CarryOverCourse;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\SenateGradeCorrection;
use App\Models\User;
use App\Services\ResultCourseSnapshotService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class SenateGradeCorrectionService
{
    public const CAPABILITY = 'senate_grade_correction';
    public const BULK_CAPABILITY = 'senate_bulk_grade_correction';
    /** @var array<int, string> */
    private array $storedEvidencePaths = [];

    public function __construct(
        private readonly StudentStatusService $studentStatusService,
        private readonly GradeCalculationService $gradeCalculationService,
        private readonly CarryOverRegistrationService $carryOverRegistrationService,
        private readonly ResultCourseSnapshotService $resultCourseSnapshotService,
    ) {}

    public function apply(array $data, User $actor, UploadedFile $evidence, string $requiredCapability = self::CAPABILITY): SenateGradeCorrection
    {
        $reference = strtoupper(trim((string) ($data['senate_reference'] ?? '')));
        if (!$this->studentStatusService->isValidSenateReference($reference)) {
            throw new InvalidArgumentException('Enter a valid Senate reference number.');
        }

        if (empty($data['decision_verified'])) {
            throw new InvalidArgumentException('Confirm that the decision was checked against the official Senate minutes or decision register.');
        }

        $resultForScope = Result::query()
            ->with(['user:id,programme_id,role', 'academicDetail:id,user_id,department_id', 'departmentCourse:id,student_course_id'])
            ->findOrFail($data['result_id'] ?? null);
        $this->authorizeActor($actor, $resultForScope->academicDetail?->department_id, $requiredCapability);
        $this->assertEligibleResult($resultForScope);

        $path = $evidence->store('senate-grade-corrections', 'local');
        $this->storedEvidencePaths[] = $path;

        try {
            return DB::transaction(function () use ($data, $actor, $reference, $path, $requiredCapability): SenateGradeCorrection {
                $result = Result::query()
                    ->with([
                        'user:id,programme_id,role',
                        'academicDetail:id,user_id,department_id',
                        'registeredCourse:id,department_course_id,student_level_id',
                        'registeredCourse.departmentCourse:id,student_course_id',
                        'registeredCourse.departmentCourse.studentCourse:id,max_ca,max_exam',
                        'departmentCourse:id,student_course_id',
                        'departmentCourse.studentCourse:id,max_ca,max_exam',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($data['result_id']);

                $this->authorizeActor($actor, $result->academicDetail?->department_id, $requiredCapability);
                $this->assertEligibleResult($result);

                if (SenateGradeCorrection::where('result_id', $result->id)->where('senate_reference', $reference)->exists()) {
                    throw new InvalidArgumentException('This Senate decision has already been applied to this result.');
                }

                $original = [
                    'ca' => (float) $result->ca_score,
                    'exam' => (float) $result->exam_score,
                    'total' => (float) $result->total_score,
                    'grade' => $result->grade,
                    'grade_point' => $result->grade_point,
                ];

                [$correctedCa, $correctedExam, $correctedTotal, $adjustment] = $this->calculateCorrectedMarks($data, $result, $original);
                $this->assertMarksWithinCourseLimits($result, $correctedCa, $correctedExam, $correctedTotal);

                $grade = $this->gradeCalculationService->getGradeAndPoint($correctedTotal);
                $this->assertCarryOverCanBeReconciled($result, $grade['grade']);

                $reason = trim((string) ($data['reason'] ?? ''));
                $reasonTag = sprintf('SENATE CORRECTION %s (%+.2f): %s', $reference, $adjustment, $reason);
                $remarks = trim(implode(' | ', array_filter([$result->remarks, $reasonTag])));
                $correctedGradePointTotal = $this->gradeCalculationService->calculateQualityPoints(
                    $grade['grade_point'],
                    $this->resultCourseSnapshotService->units($result),
                );

                $result->update([
                    'ca_score' => $correctedCa,
                    'exam_score' => $correctedExam,
                    'total_score' => $correctedTotal,
                    'grade' => $grade['grade'],
                    'grade_point' => $grade['grade_point'],
                    'grade_point_total' => $correctedGradePointTotal,
                    'remarks' => $remarks,
                ]);

                $carryOverBefore = $this->reconcileCarryOver($result, $original, $grade['grade']);
                $carryOverAfter = CarryOverCourse::query()
                    ->where('user_id', $result->user_id)
                    ->where('department_course_id', $result->department_course_id)
                    ->where('failed_session', $result->academic_session)
                    ->where('registered_course_id', $result->registered_course_id)
                    ->first()?->getAttributes();

                $correction = SenateGradeCorrection::create([
                    'result_id' => $result->id,
                    'senate_reference' => $reference,
                    'senate_decision_date' => $data['senate_decision_date'],
                    'senate_minutes_reference' => trim((string) $data['senate_minutes_reference']),
                    'evidence_path' => $path,
                    'verification_method' => trim((string) $data['verification_method']),
                    'verified_by' => $actor->id,
                    'verified_at' => now(),
                    'correction_type' => $data['correction_type'],
                    'adjustment_amount' => $adjustment,
                    'original_ca' => $original['ca'],
                    'original_exam' => $original['exam'],
                    'original_total' => $original['total'],
                    'original_grade' => $original['grade'],
                    'original_grade_point' => $original['grade_point'],
                    'corrected_ca' => $correctedCa,
                    'corrected_exam' => $correctedExam,
                    'corrected_total' => $correctedTotal,
                    'corrected_grade' => $grade['grade'],
                    'corrected_grade_point' => $grade['grade_point'],
                    'reason' => $reason,
                    'carry_over_before' => $carryOverBefore,
                    'carry_over_after' => $carryOverAfter,
                    'implemented_by' => $actor->id,
                    'implemented_at' => now(),
                ]);

                $this->recalculateAffectedGpa($result);

                return $correction->refresh();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    /** Apply one verified Senate decision to a server-selected cohort atomically. */
    public function applyBatch(array $data, User $actor, UploadedFile $evidence): int
    {
        $ids = array_values(array_unique(array_map('intval', $data['result_ids'] ?? [])));
        if ($ids === [] || count($ids) > 250) {
            throw new InvalidArgumentException('Select a cohort of 1 to 250 eligible results. Narrow the filters for a larger cohort.');
        }
        foreach (['department_id', 'level', 'department_course_id', 'academic_session', 'semester'] as $filter) {
            if (!isset($data[$filter]) || trim((string) $data[$filter]) === '') {
                throw new InvalidArgumentException('Select a department, result level, course, academic session, and semester before applying a cohort correction.');
            }
        }
        $failureScope = $data['failure_scope'] ?? 'all';
        if (!in_array($failureScope, ['all', 'failed'], true)) {
            throw new InvalidArgumentException('Select whether the Senate decision applies to all released results or failed results only.');
        }
        if (!is_numeric($data['adjustment_amount'] ?? null) || (float) $data['adjustment_amount'] <= 0) {
            throw new InvalidArgumentException('Enter a positive mark addition authorized by the Senate decision.');
        }

        $storedPathOffset = count($this->storedEvidencePaths);
        try {
            return DB::transaction(function () use ($ids, $data, $actor, $evidence, $failureScope): int {
                $results = Result::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
                if ($results->count() !== count($ids)) {
                    throw new InvalidArgumentException('The cohort changed after preview. Review the filters and preview it again.');
                }

                $scopedIds = Result::query()
                    ->whereIn('results.id', $ids)
                    ->where('results.status', 'released')
                    ->where('results.academic_session', $data['academic_session'])
                    ->where('results.semester', $data['semester'])
                    ->where('results.department_course_id', (int) $data['department_course_id'])
                    ->when($failureScope === 'failed', fn ($query) => $query->where('results.total_score', '<', 40))
                    ->where('results.academic_detail_id', '>', 0)
                    ->whereHas('user', fn ($query) => $query->where('programme_id', \App\Enums\ProgrammesEnum::Undergraduate->value))
                    ->whereHas('academicDetail', fn ($query) => $query->where('department_id', (int) $data['department_id']))
                    ->where(function ($query) use ($data): void {
                        $query->where('results.level_snapshot', (int) $data['level'])
                            ->orWhere(function ($legacy) use ($data): void {
                                $legacy->whereNull('results.level_snapshot')->whereHas('registeredCourse', fn ($registration) => $registration->where('level_snapshot', (int) $data['level']));
                            });
                    })
                    ->orderBy('results.id')->pluck('results.id')->map(fn ($id) => (int) $id)->all();
                sort($scopedIds);
                $expectedIds = $ids;
                sort($expectedIds);
                if ($scopedIds !== $expectedIds) {
                    throw new InvalidArgumentException('The selected results no longer match the exact cohort filters. Preview the cohort again.');
                }

                foreach ($results as $result) {
                    $scoped = Result::query()->with(['user:id,programme_id,role', 'academicDetail:id,user_id,department_id'])
                        ->findOrFail($result->id);
                    $this->authorizeActor($actor, $scoped->academicDetail?->department_id, self::BULK_CAPABILITY);
                    $this->assertEligibleResult($scoped);
                    if ($failureScope === 'failed' && (float) $scoped->total_score >= 40.0) {
                        throw new InvalidArgumentException('The cohort includes a result that is not a failure. Preview the failed results again.');
                    }
                    if (SenateGradeCorrection::where('result_id', $scoped->id)->where('senate_reference', strtoupper(trim((string) $data['senate_reference'])))->exists()) {
                        throw new InvalidArgumentException('This Senate reference has already been applied to a result in the cohort.');
                    }
                }

                foreach ($ids as $id) {
                    $this->apply(array_merge($data, ['result_id' => $id]), $actor, $evidence, self::BULK_CAPABILITY);
                }

                return count($ids);
            }, 3);
        } catch (Throwable $exception) {
            foreach (array_slice($this->storedEvidencePaths, $storedPathOffset) as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    private function authorizeActor(User $actor, ?int $departmentId, string $capability = self::CAPABILITY): void
    {
        $hasCapability = $actor->capabilities()
            ->where('capability', $capability)
            ->where('is_active', true)
            ->where(function ($query) use ($departmentId): void {
                $query->whereNull('department_id');
                if ($departmentId !== null) {
                    $query->orWhere('department_id', $departmentId);
                }
            })
            ->exists();

        if (!$hasCapability) {
            throw new AuthorizationException('You do not have the Senate Grade Correction capability for this department.');
        }
    }

    private function assertEligibleResult(Result $result): void
    {
        if (!$result->user?->isUndergraduate()) {
            throw new InvalidArgumentException('Senate grade corrections currently apply to undergraduate results only.');
        }
        if ($result->status !== 'released') {
            throw new InvalidArgumentException('Only released results can receive a Senate correction.');
        }
        if ($result->ca_score === null || $result->exam_score === null || $result->total_score === null) {
            throw new InvalidArgumentException('The released result is missing marks required for a safe correction.');
        }
        if (!$result->registered_course_id || !$result->department_course_id || !$result->academic_detail_id) {
            throw new InvalidArgumentException('The result is missing its student or registered-course reference.');
        }
    }

    private function calculateCorrectedMarks(array $data, Result $result, array $original): array
    {
        $type = $data['correction_type'] ?? 'total_mark_adjustment';
        if ($type === 'total_mark_adjustment') {
            $adjustment = round((float) ($data['adjustment_amount'] ?? 0), 2);
            return [$original['ca'], $original['exam'], round($original['total'] + $adjustment, 2), $adjustment];
        }

        if ($type !== 'component_correction') {
            throw new InvalidArgumentException('Select a supported Senate correction type.');
        }

        $ca = round((float) ($data['corrected_ca'] ?? -1), 2);
        $exam = round((float) ($data['corrected_exam'] ?? -1), 2);
        $total = round($ca + $exam, 2);
        return [$ca, $exam, $total, round($total - $original['total'], 2)];
    }

    private function assertMarksWithinCourseLimits(Result $result, float $ca, float $exam, float $total): void
    {
        $course = $result->departmentCourse?->studentCourse
            ?? $result->registeredCourse?->departmentCourse?->studentCourse;
        $maxCa = $course?->getMaxCa() ?? 40;
        $maxExam = $course?->getMaxExam() ?? 60;

        if ($ca < 0 || $ca > $maxCa || $exam < 0 || $exam > $maxExam || $total < 0 || $total > 100) {
            throw new InvalidArgumentException("Corrected marks must stay within the course limits (CA 0–{$maxCa}, Exam 0–{$maxExam}, Total 0–100).");
        }
    }

    private function assertCarryOverCanBeReconciled(Result $result, string $correctedGrade): void
    {
        $wasFail = $result->grade === 'F' || (float) $result->total_score < 40.0;
        $willFail = $correctedGrade === 'F';
        if ($wasFail === $willFail || !$result->department_course_id) {
            return;
        }

        $hasLaterAttempt = Result::query()
            ->where('user_id', $result->user_id)
            ->where('department_course_id', $result->department_course_id)
            ->where('id', '!=', $result->id)
            ->where('created_at', '>', $result->created_at)
            ->exists();

        $carryOver = CarryOverCourse::query()
            ->where('user_id', $result->user_id)
            ->where('department_course_id', $result->department_course_id)
            ->where('failed_session', $result->academic_session)
            ->where('registered_course_id', $result->registered_course_id)
            ->first();

        if ($hasLaterAttempt || ($carryOver && ($carryOver->is_cleared || $carryOver->retake_registered_course_id))) {
            throw new InvalidArgumentException('A later retake or carry-over decision exists. This result needs manual academic review before its pass/fail outcome can be changed.');
        }
    }

    private function reconcileCarryOver(Result $result, array $original, string $correctedGrade): ?array
    {
        $wasFail = $original['grade'] === 'F' || (float) $original['total'] < 40.0;
        $isFail = $correctedGrade === 'F' || (float) $result->total_score < 40.0;
        if (!$wasFail && !$isFail) {
            return null;
        }

        $carryOver = CarryOverCourse::query()
            ->where('user_id', $result->user_id)
            ->where('department_course_id', $result->department_course_id)
            ->where('failed_session', $result->academic_session)
            ->where('registered_course_id', $result->registered_course_id)
            ->lockForUpdate()
            ->first();
        $before = $carryOver?->getAttributes();

        if ($isFail) {
            $this->carryOverRegistrationService->recordFailedCourse($result);
        } elseif ($carryOver && !$carryOver->is_cleared) {
            $carryOver->update([
                'is_cleared' => true,
                'cleared_at' => now(),
                'cleared_result_id' => $result->id,
                'retake_session' => $result->academic_session,
                'retake_semester' => $result->semester,
                'registration_status' => 'cleared',
            ]);
        }

        return $before;
    }

    private function recalculateAffectedGpa(Result $result): void
    {
        $periods = ResultGpaRecord::query()
            ->where('user_id', $result->user_id)
            ->get(['academic_session', 'semester'])
            ->push((object) ['academic_session' => $result->academic_session, 'semester' => $result->semester])
            ->unique(fn ($period) => $period->academic_session . '|' . $period->semester);

        $student = User::query()->findOrFail($result->user_id);
        $student->loadMissing('academicDetail');
        foreach ($periods as $period) {
            $this->gradeCalculationService->processAndSaveGpaRecord(
                $student,
                (string) $period->academic_session,
                (string) $period->semester,
            );
        }
    }
}
