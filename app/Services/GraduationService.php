<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicDetail;
use App\Models\CarryOverCourse;
use App\Models\DepartmentMaxUnit;
use App\Models\GraduationEligibility;
use App\Models\GraduationList;
use App\Models\GraduationListItem;
use App\Models\Result;
use App\Models\User;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GraduationService
{
    /**
     * NUC regulatory minimum CGPA for a Bachelor's degree (Pass degree floor).
     */
    public const NUC_MIN_GRADUATION_CGPA = 1.00;

    /**
     * Standard baseline credit units required for a 4-year undergraduate degree.
     */
    public const DEFAULT_UG_REQUIRED_UNITS = 120;

    /**
     * Standard baseline credit units required for a 5-year undergraduate degree (e.g. Engineering/Law).
     */
    public const EXTENDED_UG_REQUIRED_UNITS = 150;

    public function __construct(
        protected GradeCalculationService $gradeCalculationService
    ) {}

    /**
     * Evaluate a student's graduation eligibility against all academic and regulatory criteria.
     * Persists or updates the GraduationEligibility record.
     *
     * @param User|string $student
     * @param string|null $session
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function checkEligibility(User|string $student, ?string $session = null, array $options = []): array
    {
        $user = $student instanceof User
            ? $student
            : User::with(['academicDetail.programme', 'academicDetail.department', 'programme'])->findOrFail($student);

        $academicDetail = $user->relationLoaded('academicDetail') && $user->academicDetail
            ? $user->academicDetail
            : AcademicDetail::with(['programme', 'department'])->where('user_id', $user->id)->first();

        $session = $session
            ?? $academicDetail?->acad_session
            ?? now()->format('Y') . '/' . ((int) now()->format('Y') + 1);

        // 1. CGPA & Academic Standing
        $cgpaData = $this->gradeCalculationService->calculateCGPA($user->id);
        $finalCgpa = (float) ($cgpaData['cgpa'] ?? 0.0);
        $classOfDegree = $cgpaData['class_of_degree'] ?? $this->gradeCalculationService->getClassOfDegree($finalCgpa);

        $minCgpa = (float) ($options['min_cgpa'] ?? self::NUC_MIN_GRADUATION_CGPA);
        $meetsCgpa = $finalCgpa >= $minCgpa;

        // 2. Credit Units Earned & Requirements
        $passedResults = Result::where('user_id', $user->id)
            ->whereIn('status', ['released', 'exam_officer_approved'])
            ->where('grade', '!=', 'F')
            ->get();

        $totalUnitsEarned = (int) $passedResults->sum(function ($r) {
            return $r->credit_units_snapshot ?? $r->credit_units ?? 0;
        });

        $totalUnitsRequired = $this->getRequiredUnits($user, $academicDetail, $options);
        $meetsUnits = $totalUnitsEarned >= $totalUnitsRequired;

        // 3. Compulsory Courses: General Studies (GST/GNS)
        $gstCompleted = $this->checkGeneralStudies($user);

        // 4. Compulsory Courses: SIWES
        $siwesCompleted = !empty($options['siwes_exempt']) || $this->checkSiwes($user);

        // 5. Compulsory Courses: Entrepreneurship
        $entCompleted = $this->checkEntrepreneurship($user);

        // 6. Uncleared Failed / Carry-Over Courses
        $noOutstandingCourses = $this->checkNoOutstandingCourses($user);

        // Compile Deficiencies List
        $deficiencies = [];
        if (!$meetsCgpa) {
            $deficiencies[] = "Final CGPA ({$finalCgpa}) is below the required minimum of {$minCgpa}.";
        }
        if (!$meetsUnits) {
            $deficiencies[] = "Total units earned ({$totalUnitsEarned}) is below the required {$totalUnitsRequired} units.";
        }
        if (!$gstCompleted) {
            $deficiencies[] = 'Compulsory General Studies (GST/GNS) courses have not been fully completed or passed.';
        }
        if (!$siwesCompleted) {
            $deficiencies[] = 'Mandatory SIWES / Industrial Training clearance has not been satisfied.';
        }
        if (!$entCompleted) {
            $deficiencies[] = 'Mandatory Entrepreneurship (ENT) courses have not been completed or passed.';
        }
        if (!$noOutstandingCourses) {
            $deficiencies[] = 'Student has outstanding uncleared carry-over or failed courses.';
        }

        $meetsAllRequirements = empty($deficiencies);

        // Formulate Remarks
        $remarks = $meetsAllRequirements
            ? 'Qualified for graduation conferment by Academic Board and Senate.'
            : implode(' ', $deficiencies);

        // 7. Persist to GraduationEligibility
        $eligibility = GraduationEligibility::updateOrCreate(
            [
                'user_id' => $user->id,
                'academic_session' => $session,
            ],
            [
                'academic_detail_id' => $academicDetail?->id,
                'final_cgpa' => $finalCgpa,
                'class_of_degree' => $classOfDegree,
                'total_units_earned' => $totalUnitsEarned,
                'total_units_required' => $totalUnitsRequired,
                'meets_requirements' => $meetsAllRequirements,
                'siwes_completed' => $siwesCompleted,
                'general_studies_completed' => $gstCompleted,
                'entrepreneurship_completed' => $entCompleted,
                'remarks' => $remarks,
            ]
        );

        return [
            'eligible' => $meetsAllRequirements,
            'eligibility_id' => $eligibility->id,
            'final_cgpa' => $finalCgpa,
            'class_of_degree' => $classOfDegree,
            'total_units_earned' => $totalUnitsEarned,
            'total_units_required' => $totalUnitsRequired,
            'meets_cgpa' => $meetsCgpa,
            'meets_units' => $meetsUnits,
            'siwes_completed' => $siwesCompleted,
            'general_studies_completed' => $gstCompleted,
            'entrepreneurship_completed' => $entCompleted,
            'no_outstanding_courses' => $noOutstandingCourses,
            'deficiencies' => $deficiencies,
            'eligibility' => $eligibility,
        ];
    }

    /**
     * Determine required credit units for student graduation, consulting DepartmentMaxUnit constraints.
     *
     * @param User $student
     * @param AcademicDetail|null $academicDetail
     * @param array<string, mixed> $options
     * @return int
     */
    public function getRequiredUnits(User $student, ?AcademicDetail $academicDetail = null, array $options = []): int
    {
        if (isset($options['required_units']) && (int) $options['required_units'] > 0) {
            return (int) $options['required_units'];
        }

        $detail = $academicDetail
            ?? ($student->relationLoaded('academicDetail') && $student->academicDetail ? $student->academicDetail : AcademicDetail::with('programme')->where('user_id', $student->id)->first());

        // If department max units are configured in department_max_units table
        if ($detail?->department_id) {
            $departmentCeiling = $this->getDepartmentTotalMaxUnits($detail->department_id);
            if (isset($options['use_department_max']) && $options['use_department_max'] && $departmentCeiling > 0) {
                return $departmentCeiling;
            }
        }

        // Determine by programme type (UG 4-year = 120, 5-year = 150)
        $progName = strtolower($detail?->programme?->name ?? ($student->relationLoaded('programme') ? $student->programme?->name : '') ?? '');
        if (str_contains($progName, 'engineering') || str_contains($progName, 'law') || str_contains($progName, '5-year')) {
            return self::EXTENDED_UG_REQUIRED_UNITS;
        }

        return self::DEFAULT_UG_REQUIRED_UNITS;
    }

    /**
     * Retrieve the maximum allowed semester credit units for a specific department and level
     * from the department_max_units table.
     */
    public function getDepartmentMaxUnits(int $departmentId, int $studentLevelId): ?int
    {
        $value = DepartmentMaxUnit::where('department_id', $departmentId)
            ->where('student_level_id', $studentLevelId)
            ->value('max_units');

        return $value !== null ? (int) $value : null;
    }

    /**
     * Calculate cumulative maximum units ceiling configured for a department across all its levels.
     */
    public function getDepartmentTotalMaxUnits(int $departmentId): int
    {
        return (int) DepartmentMaxUnit::where('department_id', $departmentId)->sum('max_units');
    }

    /**
     * Check if student has passed compulsory General Studies (GST/GNS) courses.
     */
    public function checkGeneralStudies(User $student): bool
    {
        return Result::where('user_id', $student->id)
            ->whereIn('status', ['released', 'exam_officer_approved'])
            ->where('grade', '!=', 'F')
            ->where(function ($q) {
                $q->where('course_code_snapshot', 'like', 'GST%')
                    ->orWhere('course_code_snapshot', 'like', 'GNS%')
                    ->orWhere('course_title_snapshot', 'like', '%General Studies%')
                    ->orWhereHas('departmentCourse.studentCourse', function ($sc) {
                        $sc->where('code', 'like', 'GST%')
                            ->orWhere('code', 'like', 'GNS%')
                            ->orWhere('title', 'like', '%General Studies%');
                    });
            })
            ->exists();
    }

    /**
     * Check if student has passed mandatory SIWES / Industrial Training courses.
     */
    public function checkSiwes(User $student): bool
    {
        return Result::where('user_id', $student->id)
            ->whereIn('status', ['released', 'exam_officer_approved'])
            ->where('grade', '!=', 'F')
            ->where(function ($q) {
                $q->where('course_code_snapshot', 'like', '%SWE%')
                    ->orWhere('course_code_snapshot', 'like', '%SIWES%')
                    ->orWhere('course_title_snapshot', 'like', '%SIWES%')
                    ->orWhere('course_title_snapshot', 'like', '%Industrial Training%')
                    ->orWhereHas('departmentCourse.studentCourse', function ($sc) {
                        $sc->where('code', 'like', '%SWE%')
                            ->orWhere('code', 'like', '%SIWES%')
                            ->orWhere('title', 'like', '%SIWES%')
                            ->orWhere('title', 'like', '%Industrial Training%');
                    });
            })
            ->exists();
    }

    /**
     * Check if student has passed mandatory Entrepreneurship (ENT) courses.
     */
    public function checkEntrepreneurship(User $student): bool
    {
        return Result::where('user_id', $student->id)
            ->whereIn('status', ['released', 'exam_officer_approved'])
            ->where('grade', '!=', 'F')
            ->where(function ($q) {
                $q->where('course_code_snapshot', 'like', 'ENT%')
                    ->orWhere('course_code_snapshot', 'like', 'ESP%')
                    ->orWhere('course_code_snapshot', 'like', 'EPS%')
                    ->orWhere('course_title_snapshot', 'like', '%Entrepreneur%')
                    ->orWhereHas('departmentCourse.studentCourse', function ($sc) {
                        $sc->where('code', 'like', 'ENT%')
                            ->orWhere('code', 'like', 'ESP%')
                            ->orWhere('code', 'like', 'EPS%')
                            ->orWhere('title', 'like', '%Entrepreneur%');
                    });
            })
            ->exists();
    }

    /**
     * Check if student has no uncleared carry-over or outstanding failed courses.
     */
    public function checkNoOutstandingCourses(User $student): bool
    {
        // 1. Check CarryOverCourse active records
        $hasActiveCarryOver = CarryOverCourse::where('user_id', $student->id)
            ->active()
            ->exists();

        if ($hasActiveCarryOver) {
            return false;
        }

        // 2. Check any failed Result that has not been subsequently passed
        $failedDepartmentCourseIds = Result::where('user_id', $student->id)
            ->whereIn('status', ['released', 'exam_officer_approved'])
            ->where('grade', 'F')
            ->pluck('department_course_id')
            ->filter()
            ->unique();

        foreach ($failedDepartmentCourseIds as $deptCourseId) {
            $hasPassed = Result::where('user_id', $student->id)
                ->where('department_course_id', $deptCourseId)
                ->whereIn('status', ['released', 'exam_officer_approved'])
                ->where('grade', '!=', 'F')
                ->exists();

            if (!$hasPassed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Run batch graduation audit for a cohort of final-year students in a department.
     *
     * @param int $departmentId
     * @param string|null $session
     * @param int|null $levelId
     * @return Collection<int, array<string, mixed>>
     */
    public function auditCohort(int $departmentId, ?string $session = null, ?int $levelId = null): Collection
    {
        $query = AcademicDetail::with(['user.academicDetail.programme', 'user.academicDetail.department', 'programme', 'department'])
            ->where('department_id', $departmentId);

        if ($levelId !== null) {
            $query->where('student_level_id', $levelId);
        }

        $academicDetails = $query->get();
        $auditResults = collect();

        foreach ($academicDetails as $detail) {
            if ($detail->user) {
                $result = $this->checkEligibility($detail->user, $session);
                $auditResults->push($result);
            }
        }

        return $auditResults;
    }

    /**
     * Mark an eligible student as officially cleared for graduation by an authorized officer.
     */
    public function clearStudent(GraduationEligibility|int $eligibility, User $officer, ?string $remarks = null): GraduationEligibility
    {
        $record = $eligibility instanceof GraduationEligibility
            ? $eligibility
            : GraduationEligibility::findOrFail($eligibility);

        if (!$record->meets_requirements) {
            throw new InvalidArgumentException('Cannot clear student for graduation: Candidate does not meet all academic requirements.');
        }

        $record->update([
            'is_cleared' => true,
            'cleared_by' => $officer->id,
            'cleared_at' => now(),
            'remarks' => $remarks ?: $record->remarks,
        ]);

        return $record->fresh();
    }

    /**
     * Stage an officially cleared graduand into the specified session GraduationList.
     */
    public function addToGraduationList(GraduationEligibility|int $eligibility, string $session, ?int $listId = null): GraduationListItem
    {
        $record = $eligibility instanceof GraduationEligibility
            ? $eligibility
            : GraduationEligibility::findOrFail($eligibility);

        if (!$record->is_cleared) {
            throw new InvalidArgumentException('Student must be cleared before being staged into the Graduation List.');
        }

        $gradList = $listId
            ? GraduationList::findOrFail($listId)
            : GraduationList::firstOrCreate(
                ['academic_session' => $session],
                [
                    'title' => "Official Graduating Pass List {$session}",
                    'venue' => 'University Convocation Arena',
                    'is_published' => false,
                ]
            );

        $student = $record->relationLoaded('student') && $record->student
            ? $record->student
            : User::with(['academicDetail.programme', 'academicDetail.department'])->find($record->user_id);

        $academicDetail = $record->relationLoaded('academicDetail') && $record->academicDetail
            ? $record->academicDetail
            : ($student?->academicDetail ?? AcademicDetail::with(['department', 'programme'])->find($record->academic_detail_id));

        $fullName = trim(($student->surname ?? '') . ' ' . ($student->firstname ?? '') . ' ' . ($student->m_name ?? ''));
        $departmentName = $academicDetail?->department?->name ?? 'Undergraduate Department';
        $programmeName = $academicDetail?->programme?->name ?? 'Bachelor of Science';

        return GraduationListItem::updateOrCreate(
            [
                'graduation_list_id' => $gradList->id,
                'user_id' => $student->id,
            ],
            [
                'academic_detail_id' => $academicDetail?->id,
                'matric_no' => $academicDetail?->matric_no ?? 'MATRIC-UNKNOWN',
                'full_name' => $fullName,
                'department' => $departmentName,
                'programme' => $programmeName,
                'final_cgpa' => $record->final_cgpa,
                'class_of_degree' => $record->class_of_degree,
                'is_present' => true,
            ]
        );
    }
}
