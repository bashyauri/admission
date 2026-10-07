<?php

namespace App\Http\Livewire\Lecturer;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\CourseAllocation;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Services\GradeCalculationService;
use App\Services\StudentStatusService;
use App\Enums\AcademicActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;
use App\Exports\ResultTemplateExport;
use App\Imports\ResultImport;
use Maatwebsite\Excel\Facades\Excel;

class ResultEntry extends Component
{
    use LivewireAlert, WithFileUploads;

    public $allocationId;
    protected ?CourseAllocation $cachedAllocation = null;
    public $results = []; // Format: [user_id => ['ca' => x, 'exam' => y]]
    public $file;
    public array $uploadPreviewRows = [];
    public array $uploadErrors = [];
    public int $previewValidCount = 0;

    // Course score weights
    public int $maxCa = 40;
    public int $maxExam = 60;

    // Session management
    public string $selectedSession;
    public string $selectedSemester = 'first';
    public array $availableSessions = [];

    // Mount the component
    public function mount(CourseAllocation $courseAllocation)
    {
        // Authorize
        if ($courseAllocation->lecturer_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this course allocation.');
        }

        $this->allocationId = $courseAllocation->id;
        $courseAllocation->loadMissing(['departmentCourse.studentCourse']);
        $this->cachedAllocation = $courseAllocation;

        $this->availableSessions = [$courseAllocation->academic_session];
        $this->selectedSession = $courseAllocation->academic_session;
        $this->selectedSemester = $courseAllocation->semester ?: 'first';

        $this->loadCourseWeights($courseAllocation);
        $this->loadStudentsAndResults();
    }

    public function getAllocationProperty(): ?CourseAllocation
    {
        return $this->getAllocation();
    }

    #[Computed]
    public function students(): \Illuminate\Support\Collection
    {
        return $this->fetchStudents($this->getAllocation());
    }

    #[Computed]
    public function assignedCoordinator(): ?\App\Models\Coordinator
    {
        $allocation = $this->getAllocation();
        if (!$allocation) {
            return null;
        }

        // Get the first student's academic detail to determine level and department
        $firstStudent = $this->students->first();
        if (!$firstStudent) {
            return null;
        }

        $ad = $firstStudent->academicDetail;
        if (!$ad) {
            return null;
        }

        // 1. Direct assigned coordinator from academic details if present
        if ($ad->coordinator_id) {
            $assignedCoord = \App\Models\Coordinator::with('user')->find($ad->coordinator_id);
            if ($assignedCoord) {
                return $assignedCoord;
            }
        }

        // 2. Resolve level directly from registration snapshot or student record (no year arithmetic)
        $levelId = $firstStudent->level_snapshot
            ?? $firstStudent->student_level_id
            ?? $ad->student_level_id
            ?? 1;
        $departmentId = $ad->department_id ?? null;
        $courseId = $ad->course_id ?? null;
        $session = $ad->admission_session ?? $allocation->academic_session;

        // Try to find coordinator with exact match first (session + level + course/department)
        $coordinator = \App\Models\Coordinator::where('student_level_id', $levelId);
        if ($session) {
            $coordinator->where('academic_session', $session);
        }

        if ($courseId) {
            $coordinator->where('course_id', $courseId);
        } elseif ($departmentId) {
            $coordinator->where('department_id', $departmentId)->whereNull('course_id');
        }

        $found = $coordinator->with('user')->first();

        // If no exact match, try without session (level + course/department only)
        if (!$found) {
            $coordinator = \App\Models\Coordinator::where('student_level_id', $levelId);

            if ($courseId) {
                $coordinator->where('course_id', $courseId);
            } elseif ($departmentId) {
                $coordinator->where('department_id', $departmentId)->whereNull('course_id');
            }

            $found = $coordinator->with('user')->first();
        }

        return $found;
    }

    protected function getAllocation(): ?CourseAllocation
    {
        if ($this->cachedAllocation === null && $this->allocationId) {
            $this->cachedAllocation = CourseAllocation::with(['departmentCourse.studentCourse'])->find($this->allocationId);
        }

        return $this->cachedAllocation;
    }

    protected function loadCourseWeights(?CourseAllocation $allocation = null): void
    {
        $allocation = $allocation ?? $this->getAllocation();
        if ($allocation) {
            $studentCourse = $allocation->departmentCourse->studentCourse ?? null;
            $this->maxCa = $studentCourse?->getMaxCa() ?? 40;
            $this->maxExam = $studentCourse?->getMaxExam() ?? 60;
        }
    }

    public function updatedSelectedSession()
    {
        $this->enforceAllocationContext();
        $this->loadStudentsAndResults();
    }

    public function updatedSelectedSemester()
    {
        $this->enforceAllocationContext();
        $this->loadStudentsAndResults();
    }

    public function updatedFile(): void
    {
        $this->uploadPreviewRows = [];
        $this->uploadErrors = [];
        $this->previewValidCount = 0;
    }

    protected function enforceAllocationContext(): void
    {
        $allocation = $this->getAllocation();
        if ($allocation) {
            $this->selectedSession = $allocation->academic_session;
            $this->selectedSemester = $allocation->semester ?: 'first';
        }
    }

    public function loadStudentsAndResults()
    {
        $allocation = $this->getAllocation();
        if (!$allocation) {
            return;
        }

        $this->enforceAllocationContext();
        $this->loadCourseWeights($allocation);

        $session = $allocation->academic_session;
        $semester = $allocation->semester ?: 'first';

        // Find registered courses for this department_course and selected session
        $registeredCourses = RegisteredCourse::with(['academicDetail.user'])
            ->join('academic_details', 'academic_details.id', '=', 'registered_courses.academic_detail_id')
            ->where('registered_courses.department_course_id', $allocation->department_course_id)
            ->where('registered_courses.academic_session', $session)
            ->orderBy('academic_details.matric_no')
            ->select('registered_courses.*')
            ->get();

        $studentIds = $registeredCourses->pluck('academicDetail.user_id')->filter()->unique()->values();
        $studentStatusService = app(StudentStatusService::class);
        $currentStatuses = $studentStatusService->getCurrentStatuses($studentIds);
        $eligibility = $studentStatusService->getActivityEligibilityForStudents(
            $registeredCourses->map(fn ($registration) => $registration->academicDetail?->user)->filter(),
            AcademicActivity::RESULT_PROCESSING,
        );
        $statusGate = Gate::forUser(Auth::user());

        // Load existing results
        $existingResults = Result::where('department_course_id', $allocation->department_course_id)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->get()
            ->keyBy('user_id');

        // Populate the results array for Livewire
        $this->results = [];
        foreach ($this->fetchStudents($allocation) as $regCourse) {
            $userId = $regCourse->academicDetail->user_id ?? null;
            if (!$userId) continue;
            $studentUser = $regCourse->academicDetail->user;
            $institutionalStatus = $currentStatuses->get($userId);
            $entryAllowed = $eligibility->get($userId, true);
            $statusLabel = $institutionalStatus
                ? ($statusGate->allows('student-status.view', $studentUser)
                    ? $institutionalStatus->status->label()
                    : 'Restricted institutional status')
                : null;

            if (isset($existingResults[$userId])) {
                $this->results[$userId] = [
                    'ca' => $existingResults[$userId]->ca_score,
                    'exam' => $existingResults[$userId]->exam_score,
                    'status' => $existingResults[$userId]->status,
                    'is_absent' => $existingResults[$userId]->remarks === 'Absent',
                    'can_enter_new_result' => $entryAllowed,
                    'institutional_status' => $statusLabel,
                ];
            } else {
                $this->results[$userId] = [
                    'ca' => null,
                    'exam' => null,
                    'status' => 'pending',
                    'is_absent' => false,
                    'can_enter_new_result' => $entryAllowed,
                    'institutional_status' => $statusLabel,
                ];
            }
        }
    }

    public function saveScore($userId)
    {
        $ca = $this->results[$userId]['ca'];
        $exam = $this->results[$userId]['exam'];
        $isAbsent = (bool) ($this->results[$userId]['is_absent'] ?? false);

        if ($isAbsent) {
            $ca = 0;
            $exam = 0;
        }

        if ($ca !== null && $ca !== '' && (!is_numeric($ca) || (float) $ca < 0 || (float) $ca > $this->maxCa)) {
            $this->alert('error', "CA Score must be between 0 and {$this->maxCa}.");
            return;
        }

        if ($exam !== null && $exam !== '' && (!is_numeric($exam) || (float) $exam < 0 || (float) $exam > $this->maxExam)) {
            $this->alert('error', "Exam Score must be between 0 and {$this->maxExam}.");
            return;
        }

        if (($this->results[$userId]['status'] ?? 'pending') !== 'pending') {
            $this->alert('error', 'Cannot edit a submitted result.');
            return;
        }

        $allocation = $this->getAllocation();
        if (!$allocation) {
            $this->alert('error', 'Course allocation not found.');
            return;
        }

        $student = RegisteredCourse::with(['academicDetail.user', 'departmentCourse.studentCourse'])
            ->where('department_course_id', $allocation->department_course_id)
            ->whereHas('academicDetail', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('academic_session', $this->selectedSession)
            ->first();

        if (!$student) {
            $this->alert('error', 'Student not found.');
            return;
        }

        if (!app(StudentStatusService::class)->canPerformAcademicActivity(
            $student->academicDetail->user,
            AcademicActivity::RESULT_PROCESSING,
        )) {
            $this->alert('warning', 'New result entry is blocked by the student’s current institutional status. Existing result history has been preserved.');
            $this->loadStudentsAndResults();
            return;
        }

        $gradeService = new GradeCalculationService();
        $total = ($ca !== null && $ca !== '' && $exam !== null && $exam !== '') ? ((float) $ca + (float) $exam) : null;
        if ($isAbsent) {
            $total = 0.0;
        }
        $grade = $isAbsent ? 'F' : ($total !== null ? $gradeService->calculateGrade($total) : null);
        $gradePoint = $isAbsent ? 0 : ($grade ? $gradeService->calculateGradePoint($grade) : null);
        $studentCourse = $student->departmentCourse?->studentCourse;
        $resultKey = [
            'user_id' => $userId,
            'registered_course_id' => $student->id,
            'academic_session' => $this->selectedSession,
            'semester' => $this->selectedSemester,
        ];
        $existingResult = Result::query()->where($resultKey)->first();
        $isUndergraduate = $student->academicDetail?->user?->isUndergraduate() ?? false;
        $creditUnits = $isUndergraduate
            ? (int) ($existingResult?->credit_units_snapshot
                ?? $student->credit_units_snapshot
                ?? $student->units
                ?? $allocation->departmentCourse?->units
                ?? 0)
            : (int) $student->units;
        $attemptSnapshots = [];

        if ($isUndergraduate) {
            $attemptSnapshots = [
                'course_code_snapshot' => $existingResult?->course_code_snapshot
                    ?? $student->course_code_snapshot
                    ?? $studentCourse?->code,
                'course_title_snapshot' => $existingResult?->course_title_snapshot
                    ?? $student->course_title_snapshot
                    ?? $studentCourse?->title,
                'credit_units_snapshot' => $creditUnits,
                'semester_snapshot' => $existingResult?->semester_snapshot
                    ?? $student->semester_snapshot
                    ?? $student->semester
                    ?? ($studentCourse?->semester !== null ? (string) $studentCourse->semester : null),
                'level_snapshot' => $existingResult?->level_snapshot
                    ?? $student->level_snapshot
                    ?? $student->student_level_id
                    ?? $studentCourse?->student_level_id,
            ];
        }

        Result::updateOrCreate(
            $resultKey,
            [
                'department_course_id' => $allocation->department_course_id,
                'academic_detail_id' => $student->academic_detail_id,
                'ca_score' => ($ca === '' || $ca === null) ? null : $ca,
                'exam_score' => ($exam === '' || $exam === null) ? null : $exam,
                'total_score' => $total,
                'grade' => $grade,
                'grade_point' => $gradePoint,
                'credit_units' => $creditUnits,
                'grade_point_total' => $gradePoint !== null ? $gradePoint * $creditUnits : null,
                'status' => 'pending',
                'lecturer_id' => Auth::id(),
                'remarks' => $isAbsent ? 'Absent' : null,
                ...$attemptSnapshots,
            ]
        );

        $this->alert('success', 'Score saved successfully.');
    }

    public function submitAll(): void
    {
        $this->submitResults();
    }

    public function submitResults()
    {
        $allocation = $this->getAllocation();
        if (!$allocation) {
            $this->alert('error', 'Course allocation not found.');
            return;
        }

        $session  = $this->selectedSession;
        $semester = $this->selectedSemester;

        // 1. Load all pending results with academicDetail and registeredCourse fields we need for coordinator lookup
        $results = Result::where('department_course_id', $allocation->department_course_id)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->where('status', 'pending')
            ->with([
                'academicDetail:id,user_id,course_id,department_id,student_level_id,admission_session,coordinator_id',
                'registeredCourse:id,student_level_id,level_snapshot',
            ])
            ->get();

        if ($results->isEmpty()) {
            $this->alert('warning', 'No pending results to submit.');
            return;
        }

        // 2. Validate completeness before touching anything
        $incomplete = $results->filter(fn ($r) =>
            $r->remarks !== 'Absent' && ($r->ca_score === null || $r->exam_score === null)
        );

        if ($incomplete->isNotEmpty()) {
            $this->alert('error', 'Cannot submit results. Some students do not have both CA and Exam scores entered, or are not marked as Absent.');
            return;
        }

        // 3. Bulk-resolve coordinators in ONE query per (course+level+session) combination
        //    Use level_snapshot from result / registered_course / academicDetail (no year arithmetic)
        $resultKeys = $results->map(function ($r) {
            $ad = $r->academicDetail;
            $levelId = $r->level_snapshot 
                ?? $r->registeredCourse?->level_snapshot 
                ?? $r->registeredCourse?->student_level_id 
                ?? $ad?->student_level_id 
                ?? 1;

            return [
                'course_id'       => $ad?->course_id,
                'department_id'   => $ad?->department_id,
                'level_id'        => $levelId,
                'admission'       => $ad?->admission_session,
            ];
        })->unique(fn ($k) => $k['course_id'] . '|' . $k['level_id'] . '|' . $k['admission'])->values();

        $directCoordIds = $results->pluck('academicDetail.coordinator_id')->filter()->unique()->values()->all();

        // Single bulk fetch of all potentially matching coordinators
        $coordinators = \App\Models\Coordinator::where(function ($q) use ($resultKeys, $directCoordIds) {
            if (!empty($directCoordIds)) {
                $q->whereIn('id', $directCoordIds);
            }
            foreach ($resultKeys as $key) {
                $q->orWhere(function ($sub) use ($key) {
                    if ($key['course_id']) {
                        $sub->where('course_id', $key['course_id'])
                            ->where('student_level_id', $key['level_id']);
                        if ($key['admission']) {
                            $sub->where(function ($s2) use ($key) {
                                $s2->where('academic_session', $key['admission'])
                                   ->orWhereNull('academic_session');
                            });
                        }
                    } elseif ($key['department_id']) {
                        $sub->where('department_id', $key['department_id'])
                            ->where('student_level_id', $key['level_id'])
                            ->whereNull('course_id');
                    }
                });
            }
        })->get();

        // Build a fast lookup: "courseId|levelId|admissionSession" => coordinator
        $coordMap = [];
        foreach ($coordinators as $coord) {
            $coordMap['id|' . $coord->id] = $coord;
            // Prefer course+level+session exact match
            $key = $coord->course_id . '|' . $coord->student_level_id . '|' . $coord->academic_session;
            $coordMap[$key] = $coordMap[$key] ?? $coord;
            // Also index course+level (session-agnostic fallback)
            $fallbackKey = $coord->course_id . '|' . $coord->student_level_id . '|';
            $coordMap[$fallbackKey] = $coordMap[$fallbackKey] ?? $coord;
            // Dept-level fallback
            if (!$coord->course_id && $coord->department_id) {
                $deptKey = 'dept|' . $coord->department_id . '|' . $coord->student_level_id;
                $coordMap[$deptKey] = $coordMap[$deptKey] ?? $coord;
            }
        }

        // 4. Resolve coordinator per result using the pre-built map (zero extra queries)
        $resolveCoordinator = function (\App\Models\Result $result) use ($coordMap): ?\App\Models\Coordinator {
            $ad = $result->academicDetail;
            if (!$ad) {
                return null;
            }

            // 1. Direct coordinator assignment from PIN gen / registration if present
            if ($ad->coordinator_id && isset($coordMap['id|' . $ad->coordinator_id])) {
                return $coordMap['id|' . $ad->coordinator_id];
            }

            // 2. Resolve level directly from registration snapshot or student record (no year arithmetic)
            $levelId = $result->level_snapshot 
                ?? $result->registeredCourse?->level_snapshot 
                ?? $result->registeredCourse?->student_level_id 
                ?? $ad->student_level_id 
                ?? 1;

            $exactKey    = $ad->course_id . '|' . $levelId . '|' . $ad->admission_session;
            $courseKey   = $ad->course_id . '|' . $levelId . '|';
            $deptKey     = 'dept|' . $ad->department_id . '|' . $levelId;
            $legacyCoord = $ad->coordinator_id ? ($coordMap['id|' . $ad->coordinator_id] ?? \App\Models\Coordinator::find($ad->coordinator_id)) : null;

            return $coordMap[$exactKey]
                ?? $coordMap[$courseKey]
                ?? $coordMap[$deptKey]
                ?? $legacyCoord;
        };

        // 5. Batch all updates: separate result IDs by coordinator
        //    Then do ONE update per coordinator group instead of N individual updates
        $byCoordinator = []; // coordinator_id => [result_ids]
        $unassignedStudents = []; // student details for error message
        $updated = 0;

        foreach ($results as $result) {
            $coordinator = $resolveCoordinator($result);

            if ($coordinator) {
                $byCoordinator[$coordinator->id][] = $result->id;
                $updated++;
            } else {
                $ad = $result->academicDetail;
                $levelId = $result->level_snapshot 
                    ?? $result->registeredCourse?->level_snapshot 
                    ?? $result->registeredCourse?->student_level_id 
                    ?? $ad?->student_level_id 
                    ?? 1;

                \Log::warning("No coordinator found for student {$result->user_id} course={$ad?->course_id} level={$levelId} session={$ad?->admission_session}");

                // Collect student details for error message
                $unassignedStudents[] = [
                    'matric_no' => $ad?->matric_no ?? 'N/A',
                    'name' => $result->user?->surname . ' ' . $result->user?->firstname ?? 'Unknown',
                    'level' => $levelId ?? 'N/A',
                ];
            }
        }

        // Block submission if any student lacks a coordinator
        if (!empty($unassignedStudents)) {
            $studentList = collect($unassignedStudents)->map(fn ($s) => "{$s['matric_no']} ({$s['name']}, {$s['level']}L)")->join(', ');
            $this->alert('error', "Cannot submit results. " . count($unassignedStudents) . " student(s) do not have coordinators assigned: {$studentList}. Please contact the administrator to assign coordinators to these students before submitting.");
            return;
        }

        // Single UPDATE per coordinator group (replaces N individual ->update() calls)
        $coordinatorNames = [];
        foreach ($byCoordinator as $coordinatorId => $resultIds) {
            $coordinator = \App\Models\Coordinator::with('user')->find($coordinatorId);
            $coordinatorName = $coordinator?->user ? ($coordinator->user->firstname . ' ' . $coordinator->user->surname) : "Coordinator #{$coordinatorId}";
            $coordinatorNames[] = "{$coordinatorName} (" . count($resultIds) . " results)";

            \Illuminate\Support\Facades\DB::table('results')
                ->whereIn('id', $resultIds)
                ->update([
                    'status'         => 'submitted',
                    'coordinator_id' => $coordinatorId,
                    'updated_at'     => now(),
                ]);
        }

        if ($updated > 0) {
            $coordinatorList = implode(', ', $coordinatorNames);
            $this->alert('success', "{$updated} result(s) submitted to coordinator(s): {$coordinatorList}");
            $this->loadStudentsAndResults();
        } else {
            $this->alert('info', 'No pending results to submit.');
        }
    }

    public function downloadTemplate()
    {
        $allocation = $this->getAllocation();
        $this->loadCourseWeights($allocation);
        $studentCourse = $allocation?->departmentCourse?->studentCourse ?? null;
        $courseCode = $studentCourse->code ?? 'Course';
        $session = $allocation?->academic_session ?? $this->selectedSession;
        $fileName = 'Result_Template_' . str_replace(' ', '_', $courseCode) . '_' . str_replace('/', '-', $session) . '.csv';
        $students = $this->fetchStudents($allocation);
        return Excel::download(new ResultTemplateExport($students, $this->maxCa, $this->maxExam), $fileName);
    }

    public function previewResults(): void
    {
        $this->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:2048',
        ]);

        $allocation = $this->getAllocation();
        if (!$allocation) {
            $this->alert('error', 'Course allocation not found.');
            return;
        }

        $this->loadCourseWeights($allocation);

        try {
            $import = new ResultImport(
                $allocation,
                $allocation->academic_session,
                $allocation->semester ?: 'first',
                false,
                $this->maxCa,
                $this->maxExam
            );
            Excel::import($import, $this->file->getRealPath());

            $this->uploadPreviewRows = $import->previewRows;
            $this->uploadErrors = $import->errors;
            $this->previewValidCount = $import->successCount;
        } catch (\Exception $e) {
            $this->alert('error', 'Unable to preview file: ' . $e->getMessage());
        }
    }

    public function importResults(): void
    {
        if (!$this->file || $this->previewValidCount === 0) {
            $this->alert('error', 'Preview a file with at least one valid result before importing.');
            return;
        }

        $allocation = $this->getAllocation();
        if (!$allocation) {
            $this->alert('error', 'Course allocation not found.');
            return;
        }

        $this->loadCourseWeights($allocation);

        try {
            $import = new ResultImport(
                $allocation,
                $allocation->academic_session,
                $allocation->semester ?: 'first',
                true,
                $this->maxCa,
                $this->maxExam
            );
            Excel::import($import, $this->file->getRealPath());

            if ($import->successCount > 0) {
                $this->alert('success', $import->successCount . ' records imported successfully!');
            }

            if (count($import->errors) > 0) {
                $this->alert('warning', count($import->errors) . ' record(s) were not imported. First: ' . $import->errors[0]);
            }

            $this->loadStudentsAndResults();
            $this->file = null;
            $this->uploadPreviewRows = [];
            $this->uploadErrors = [];
            $this->previewValidCount = 0;
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to import: ' . $e->getMessage());
        }
    }

    public function discardUploadPreview(): void
    {
        $this->file = null;
        $this->uploadPreviewRows = [];
        $this->uploadErrors = [];
        $this->previewValidCount = 0;
    }

    protected function fetchStudents(?CourseAllocation $allocation): \Illuminate\Support\Collection
    {
        if (!$allocation) {
            return collect();
        }

        return RegisteredCourse::with(['academicDetail.user'])
            ->join('academic_details', 'academic_details.id', '=', 'registered_courses.academic_detail_id')
            ->where('registered_courses.department_course_id', $allocation->department_course_id)
            ->where('registered_courses.academic_session', $allocation->academic_session)
            ->orderBy('academic_details.matric_no')
            ->select('registered_courses.*')
            ->get();
    }

    public function render()
    {
        $allocation = $this->getAllocation();
        $this->loadCourseWeights($allocation);
        $students = $this->fetchStudents($allocation);

        return view('livewire.lecturer.result-entry', [
            'allocation' => $allocation,
            'students' => $students,
            'maxCa' => $this->maxCa,
            'maxExam' => $this->maxExam,
        ])->layout('layouts.app');
    }
}
