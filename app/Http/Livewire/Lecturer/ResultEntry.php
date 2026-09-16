<?php

namespace App\Http\Livewire\Lecturer;

use Livewire\Component;
use App\Models\CourseAllocation;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Services\GradeCalculationService;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\WithFileUploads;
use App\Exports\ResultTemplateExport;
use App\Imports\ResultImport;
use Maatwebsite\Excel\Facades\Excel;

class ResultEntry extends Component
{
    use LivewireAlert, WithFileUploads;

    public $allocationId;
    public CourseAllocation $allocation;
    public $students = [];
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

        $this->allocation = $courseAllocation->loadMissing(['departmentCourse.studentCourse']);
        $this->allocationId = $courseAllocation->id;

        $this->availableSessions = [$this->allocation->academic_session];
        $this->selectedSession = $this->allocation->academic_session;
        $this->selectedSemester = $this->allocation->semester ?: 'first';

        $this->loadCourseWeights();
        $this->loadStudentsAndResults();
    }

    public function hydrate()
    {
        if (isset($this->allocation)) {
            $this->allocation->loadMissing(['departmentCourse.studentCourse']);
        }
        if ($this->students && method_exists($this->students, 'loadMissing')) {
            $this->students->loadMissing(['academicDetail.user']);
        }
        $this->loadCourseWeights();
    }

    protected function loadCourseWeights(): void
    {
        if (isset($this->allocation)) {
            $studentCourse = $this->allocation->departmentCourse->studentCourse ?? null;
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
        $this->selectedSession = $this->allocation->academic_session;
        $this->selectedSemester = $this->allocation->semester ?: 'first';
    }

    public function loadStudentsAndResults()
    {
        $this->enforceAllocationContext();
        $this->loadCourseWeights();

        $session = $this->allocation->academic_session;
        $semester = $this->allocation->semester ?: 'first';

        // Find registered courses for this department_course and selected session
        $registeredCourses = RegisteredCourse::with(['academicDetail.user'])
            ->where('department_course_id', $this->allocation->department_course_id)
            ->where('academic_session', $session)
            ->get()
            ->sortBy(function ($rc) {
                return $rc->academicDetail->matric_no ?? '';
            })
            ->values();

        $this->students = $registeredCourses;

        // Load existing results
        $existingResults = Result::where('department_course_id', $this->allocation->department_course_id)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->get()
            ->keyBy('user_id');

        // Populate the results array for Livewire
        $this->results = [];
        foreach ($this->students as $regCourse) {
            $userId = $regCourse->academicDetail->user_id ?? null;
            if (!$userId) continue;
            if (isset($existingResults[$userId])) {
                $this->results[$userId] = [
                    'ca' => $existingResults[$userId]->ca_score,
                    'exam' => $existingResults[$userId]->exam_score,
                    'status' => $existingResults[$userId]->status,
                    'is_absent' => $existingResults[$userId]->remarks === 'Absent',
                ];
            } else {
                $this->results[$userId] = [
                    'ca' => null,
                    'exam' => null,
                    'status' => 'pending',
                    'is_absent' => false,
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

        $student = RegisteredCourse::with('academicDetail')->where('department_course_id', $this->allocation->department_course_id)
            ->whereHas('academicDetail', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('academic_session', $this->selectedSession)
            ->first();

        if (!$student) {
            $this->alert('error', 'Student not found.');
            return;
        }

        $gradeService = new GradeCalculationService();
        $total = ($ca !== null && $ca !== '' && $exam !== null && $exam !== '') ? ((float) $ca + (float) $exam) : null;
        if ($isAbsent) {
            $total = 0.0;
        }
        $grade = $isAbsent ? 'F' : ($total !== null ? $gradeService->calculateGrade($total) : null);
        $gradePoint = $isAbsent ? 0 : ($grade ? $gradeService->calculateGradePoint($grade) : null);
        $creditUnits = $student->units;

        Result::updateOrCreate(
            [
                'user_id' => $userId,
                'registered_course_id' => $student->id,
                'academic_session' => $this->selectedSession,
                'semester' => $this->selectedSemester,
            ],
            [
                'department_course_id' => $this->allocation->department_course_id,
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
        $session  = $this->selectedSession;
        $semester = $this->selectedSemester;

        // 1. Load all pending results with academicDetail fields we need for coordinator lookup
        $results = Result::where('department_course_id', $this->allocation->department_course_id)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->where('status', 'pending')
            ->with(['academicDetail:id,user_id,course_id,department_id,student_level_id,admission_session,coordinator_id'])
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
        //    instead of firing up to 5 queries per student inside the loop.
        $acadDetails = $results->map->academicDetail->filter();

        // Gather all unique (course_id, student_level_id, admission_session) combos
        $cohortKeys = $acadDetails->map(fn ($ad) => [
            'course_id'       => $ad->course_id,
            'department_id'   => $ad->department_id,
            'level_id'        => $ad->student_level_id,
            'admission'       => $ad->admission_session,
        ])->unique(fn ($k) => $k['course_id'] . '|' . $k['level_id'] . '|' . $k['admission'])->values();

        // Single bulk fetch of all potentially matching coordinators
        $coordinators = \App\Models\Coordinator::where(function ($q) use ($cohortKeys) {
            foreach ($cohortKeys as $key) {
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

        // 4. Resolve coordinator per academicDetail using the pre-built map (zero extra queries)
        $resolveCoordinator = function (\App\Models\AcademicDetail $ad) use ($coordMap): ?\App\Models\Coordinator {
            $exactKey    = $ad->course_id . '|' . $ad->student_level_id . '|' . $ad->admission_session;
            $courseKey   = $ad->course_id . '|' . $ad->student_level_id . '|';
            $deptKey     = 'dept|' . $ad->department_id . '|' . $ad->student_level_id;
            $legacyCoord = $ad->coordinator_id ? \App\Models\Coordinator::find($ad->coordinator_id) : null;

            return $coordMap[$exactKey]
                ?? $coordMap[$courseKey]
                ?? $coordMap[$deptKey]
                ?? $legacyCoord;
        };

        // 5. Batch all updates: separate result IDs by coordinator
        //    Then do ONE update per coordinator group instead of N individual updates
        $byCoordinator = []; // coordinator_id => [result_ids]
        $unassignedCount = 0;
        $updated = 0;

        foreach ($results as $result) {
            $ad = $result->academicDetail;
            if (!$ad) {
                $unassignedCount++;
                continue;
            }

            $coordinator = $resolveCoordinator($ad);

            if ($coordinator) {
                $byCoordinator[$coordinator->id][] = $result->id;
                $updated++;
            } else {
                $unassignedCount++;
                \Log::warning("No coordinator found for student {$ad->user_id} course={$ad->course_id} level={$ad->student_level_id} session={$ad->admission_session}");
            }
        }

        // Single UPDATE per coordinator group (replaces N individual ->update() calls)
        foreach ($byCoordinator as $coordinatorId => $resultIds) {
            \Illuminate\Support\Facades\DB::table('results')
                ->whereIn('id', $resultIds)
                ->update([
                    'status'         => 'submitted',
                    'coordinator_id' => $coordinatorId,
                    'updated_at'     => now(),
                ]);
        }

        if ($updated > 0) {
            $this->alert('success', "{$updated} result(s) submitted to coordinator(s) successfully.");
            $this->loadStudentsAndResults();
        } elseif ($unassignedCount > 0) {
            $this->alert('warning', "No coordinator found for {$unassignedCount} student(s). Please ensure coordinators are assigned.");
        } else {
            $this->alert('info', 'No pending results to submit.');
        }
    }

    public function downloadTemplate()
    {
        $this->loadCourseWeights();
        $studentCourse = $this->allocation->departmentCourse->studentCourse ?? null;
        $courseCode = $studentCourse->code ?? 'Course';
        $fileName = 'Result_Template_' . str_replace(' ', '_', $courseCode) . '_' . str_replace('/', '-', $this->allocation->academic_session) . '.csv';
        return Excel::download(new ResultTemplateExport($this->students, $this->maxCa, $this->maxExam), $fileName);
    }

    public function previewResults(): void
    {
        $this->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:2048',
        ]);

        $this->loadCourseWeights();

        try {
            $import = new ResultImport(
                $this->allocation,
                $this->allocation->academic_session,
                $this->allocation->semester ?: 'first',
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

        $this->loadCourseWeights();

        try {
            $import = new ResultImport(
                $this->allocation,
                $this->allocation->academic_session,
                $this->allocation->semester ?: 'first',
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

    public function render()
    {
        $this->loadCourseWeights();
        return view('livewire.lecturer.result-entry', [
            'maxCa' => $this->maxCa,
            'maxExam' => $this->maxExam,
        ])->layout('layouts.app');
    }
}
