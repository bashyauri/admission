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

    protected function enforceAllocationContext(): void
    {
        $this->selectedSession = $this->allocation->academic_session;
        $this->selectedSemester = $this->allocation->semester ?: 'first';
    }

    public function loadStudentsAndResults()
    {
        $this->enforceAllocationContext();

        $session = $this->allocation->academic_session;
        $semester = $this->allocation->semester ?: 'first';

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

        $session = $this->allocation->academic_session;
        $semester = $this->allocation->semester ?: 'first';
        $gradeService = new GradeCalculationService();

        $total = floatval($ca ?? 0) + floatval($exam ?? 0);
        $grade = $gradeService->calculateGrade($total);
        $gradePoint = $gradeService->calculateGradePoint($grade);

        $regCourse = $this->students->first(function ($rc) use ($userId) {
            return ($rc->academicDetail->user_id ?? null) == $userId;
        });

        if (!$regCourse) {
            $this->alert('error', 'This student is not registered for this allocated course and session.');
            return;
        }

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
        $session = $this->allocation->academic_session;
        $semester = $this->allocation->semester ?: 'first';

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
        $fileName = 'Result_Template_' . str_replace(' ', '_', $courseCode) . '_' . str_replace('/', '-', $this->allocation->academic_session) . '.csv';
        return Excel::download(new ResultTemplateExport($this->students), $fileName);
    }

    public function importResults()
    {
        $this->validate([
            'file' => 'required|mimes:csv,txt,xlsx|max:2048',
        ]);

        try {
            $import = new ResultImport($this->allocation, $this->allocation->academic_session, $this->allocation->semester ?: 'first');
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
