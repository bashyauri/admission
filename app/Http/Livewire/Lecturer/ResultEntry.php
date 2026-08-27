<?php

namespace App\Http\Livewire\Lecturer;

use Livewire\Component;
use App\Models\CourseAllocation;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\Setting;
use App\Services\GradeCalculationService;
use App\Services\AcademicSessionService;
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

        $this->allocation = $courseAllocation;
        $this->allocationId = $courseAllocation->id;

        // Resolve default session from AcademicSessionService
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $service = new AcademicSessionService();
        $defaultSession = $service->getAcademicSession($user);

        // Build available sessions from the settings table
        $sessionKeys = ['ACADEMIC_SESSION', 'HOD_ACADEMIC_SESSION', 'PG_ACADEMIC_SESSION', 'ADMIN_ACADEMIC_SESSION'];
        $dbSessions = Setting::whereIn('key', $sessionKeys)->pluck('value')->filter()->unique()->values()->toArray();

        $this->availableSessions = array_values(array_unique(array_merge(
            $dbSessions,
            [$defaultSession]
        )));
        sort($this->availableSessions);

        $this->selectedSession = $defaultSession;

        $this->loadStudentsAndResults();
    }

    public function updatedSelectedSession()
    {
        $this->loadStudentsAndResults();
    }

    public function updatedSelectedSemester()
    {
        $this->loadStudentsAndResults();
    }

    public function loadStudentsAndResults()
    {
        $session = $this->selectedSession;
        $semester = $this->selectedSemester;

        // Find registered courses for this department_course and selected session
        $registeredCourses = RegisteredCourse::with(['academicDetail.user'])
            ->where('department_course_id', $this->allocation->department_course_id)
            ->where('academic_session', $session)
            ->get();

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
                ];
            } else {
                $this->results[$userId] = [
                    'ca' => null,
                    'exam' => null,
                    'status' => 'pending',
                ];
            }
        }
    }

    public function saveScore($userId)
    {
        $ca = $this->results[$userId]['ca'];
        $exam = $this->results[$userId]['exam'];

        if ($ca !== null && $ca !== '' && ($ca < 0 || $ca > 40)) {
            $this->alert('error', 'CA Score must be between 0 and 40.');
            return;
        }

        if ($exam !== null && $exam !== '' && ($exam < 0 || $exam > 60)) {
            $this->alert('error', 'Exam Score must be between 0 and 60.');
            return;
        }

        if (($this->results[$userId]['status'] ?? 'pending') !== 'pending') {
            $this->alert('error', 'Cannot edit a submitted result.');
            return;
        }

        $session = $this->selectedSession;
        $semester = $this->selectedSemester;
        $gradeService = new GradeCalculationService();

        $total = floatval($ca ?? 0) + floatval($exam ?? 0);
        $grade = $gradeService->calculateGrade($total);
        $gradePoint = $gradeService->calculateGradePoint($grade);

        $regCourse = $this->students->first(function ($rc) use ($userId) {
            return ($rc->academicDetail->user_id ?? null) == $userId;
        });
        $creditUnits = $regCourse->units ?? 0;

        Result::updateOrCreate(
            [
                'user_id' => $userId,
                'registered_course_id' => $regCourse->id,
                'academic_session' => $session,
                'semester' => $semester,
            ],
            [
                'department_course_id' => $this->allocation->department_course_id,
                'academic_detail_id' => $regCourse->academic_detail_id,
                'ca_score' => ($ca === '' || $ca === null) ? null : $ca,
                'exam_score' => ($exam === '' || $exam === null) ? null : $exam,
                'total_score' => $total,
                'grade' => $grade,
                'grade_point' => $gradePoint,
                'credit_units' => $creditUnits,
                'grade_point_total' => $gradePoint * $creditUnits,
                'status' => 'pending',
                'lecturer_id' => Auth::id(),
            ]
        );

        $this->results[$userId]['status'] = 'pending'; // Refresh local state
        $this->alert('success', 'Score saved!', ['toast' => true, 'position' => 'top-end', 'timer' => 1500]);
    }

    public function submitAll()
    {
        $session = $this->selectedSession;
        $semester = $this->selectedSemester;

        $updated = Result::where('department_course_id', $this->allocation->department_course_id)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->where('status', 'pending')
            ->update(['status' => 'submitted']);

        if ($updated > 0) {
            $this->alert('success', "{$updated} results submitted to HOD successfully.");
            $this->loadStudentsAndResults();
        } else {
            $this->alert('info', 'No pending results to submit.');
        }
    }

    public function downloadTemplate()
    {
        $courseCode = $this->allocation->departmentCourse->studentCourse->code ?? 'Course';
        $fileName = 'Result_Template_' . str_replace(' ', '_', $courseCode) . '_' . str_replace('/', '-', $this->selectedSession) . '.csv';
        return Excel::download(new ResultTemplateExport($this->students), $fileName);
    }

    public function importResults()
    {
        $this->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:2048',
        ]);

        try {
            $import = new ResultImport($this->allocation, $this->selectedSession, $this->selectedSemester);
            Excel::import($import, $this->file->getRealPath());

            if (count($import->errors) > 0) {
                $this->alert('warning', count($import->errors) . ' error(s). First: ' . $import->errors[0]);
            }

            if ($import->successCount > 0) {
                $this->alert('success', $import->successCount . ' records imported successfully!');
            }

            $this->loadStudentsAndResults();
            $this->file = null;
        } catch (\Exception $e) {
            $this->alert('error', 'Failed to import: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.lecturer.result-entry')->layout('layouts.app');
    }
}
