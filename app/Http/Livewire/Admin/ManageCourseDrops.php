<?php

namespace App\Http\Livewire\Admin;

use App\Models\CourseDropAudit;
use App\Models\Department;
use App\Models\Programme;
use App\Models\RegisteredCourse;
use App\Models\StudentLevel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ManageCourseDrops extends Component
{
    use LivewireAlert;

    public string $academicSession = '';
    public string $courseCodes = '';
    public ?int $departmentId = null;
    public ?int $programmeId = null;
    public ?int $studentLevelId = null;
    public string $note = '';
    public string $confirmationText = '';

    public int $previewMatchedCount = 0;
    public int $lastDroppedCount = 0;
    public array $previewRows = [];

    public function mount(): void
    {
        $this->academicSession = (string) config('remita.settings.academic_session', '');
    }

    public function previewDrop(): void
    {
        $validated = $this->validate($this->rules());
        $codes = $this->parseCourseCodes($validated['courseCodes']);

        if ($codes === []) {
            $this->addError('courseCodes', 'Enter at least one valid course code.');
            return;
        }

        $matchedCount = (clone $this->buildMatchingQuery($validated, $codes))->count();
        $this->previewMatchedCount = $matchedCount;
        $this->previewRows = $this->getPreviewRows($validated, $codes);

        $this->createAudit(
            actionType: 'preview',
            courseCodes: $codes,
            matchedCount: $matchedCount,
            droppedCount: 0,
            validated: $validated
        );

        $this->alert('info', "Preview complete: {$matchedCount} registration(s) match your filters.");
    }

    public function executeDrop(): void
    {
        $validated = $this->validate(array_merge($this->rules(), [
            'confirmationText' => ['required', 'in:DROP'],
        ]));

        $codes = $this->parseCourseCodes($validated['courseCodes']);
        if ($codes === []) {
            $this->addError('courseCodes', 'Enter at least one valid course code.');
            return;
        }

        $droppedCount = DB::transaction(function () use ($validated, $codes): int {
            $query = $this->buildMatchingQuery($validated, $codes);
            $matchedCount = (clone $query)->count();

            if ($matchedCount === 0) {
                $this->createAudit(
                    actionType: 'execute',
                    courseCodes: $codes,
                    matchedCount: 0,
                    droppedCount: 0,
                    validated: $validated
                );

                return 0;
            }

            $courseIds = (clone $query)->pluck('registered_courses.id');
            $deleted = RegisteredCourse::query()->whereIn('id', $courseIds)->delete();

            $this->createAudit(
                actionType: 'execute',
                courseCodes: $codes,
                matchedCount: $matchedCount,
                droppedCount: $deleted,
                validated: $validated
            );

            return $deleted;
        });

        $this->lastDroppedCount = $droppedCount;
        $this->previewMatchedCount = 0;
        $this->confirmationText = '';
        $this->previewRows = [];

        $this->alert('success', "Course drop completed. {$droppedCount} registration(s) removed.");
    }

    private function rules(): array
    {
        return [
            'academicSession' => ['required', 'string', 'max:50'],
            'courseCodes' => ['required', 'string', 'max:1000'],
            'departmentId' => ['nullable', 'integer', 'exists:departments,id'],
            'programmeId' => ['nullable', 'integer', 'exists:programmes,id'],
            'studentLevelId' => ['nullable', 'integer', 'exists:student_levels,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function parseCourseCodes(string $rawCodes): array
    {
        $codes = collect(explode(',', $rawCodes))
            ->map(static fn(string $code): string => strtoupper(trim($code)))
            ->filter(static fn(string $code): bool => $code !== '')
            ->unique()
            ->values()
            ->all();

        return $codes;
    }

    private function buildMatchingQuery(array $validated, array $codes)
    {
        $query = RegisteredCourse::query()
            ->join('department_courses', 'registered_courses.department_course_id', '=', 'department_courses.id')
            ->join('student_courses', 'department_courses.student_course_id', '=', 'student_courses.id')
            ->join('academic_details', 'registered_courses.academic_detail_id', '=', 'academic_details.id')
            ->where('registered_courses.academic_session', $validated['academicSession'])
            ->whereIn('student_courses.code', $codes)
            ->select('registered_courses.id');

        if (!empty($validated['departmentId'])) {
            $query->where('academic_details.department_id', (int) $validated['departmentId']);
        }

        if (!empty($validated['programmeId'])) {
            $query->where('academic_details.programme_id', (int) $validated['programmeId']);
        }

        if (!empty($validated['studentLevelId'])) {
            $query->where('academic_details.student_level_id', (int) $validated['studentLevelId']);
        }

        return $query;
    }

    private function getPreviewRows(array $validated, array $codes): array
    {
        return RegisteredCourse::query()
            ->join('department_courses', 'registered_courses.department_course_id', '=', 'department_courses.id')
            ->join('student_courses', 'department_courses.student_course_id', '=', 'student_courses.id')
            ->join('academic_details', 'registered_courses.academic_detail_id', '=', 'academic_details.id')
            ->join('users', 'academic_details.user_id', '=', 'users.id')
            ->leftJoin('departments', 'academic_details.department_id', '=', 'departments.id')
            ->where('registered_courses.academic_session', $validated['academicSession'])
            ->whereIn('student_courses.code', $codes)
            ->when(
                !empty($validated['departmentId']),
                fn($query) => $query->where('academic_details.department_id', (int) $validated['departmentId'])
            )
            ->when(
                !empty($validated['programmeId']),
                fn($query) => $query->where('academic_details.programme_id', (int) $validated['programmeId'])
            )
            ->when(
                !empty($validated['studentLevelId']),
                fn($query) => $query->where('academic_details.student_level_id', (int) $validated['studentLevelId'])
            )
            ->orderBy('users.surname')
            ->orderBy('users.firstname')
            ->limit(12)
            ->get([
                'registered_courses.id',
                'student_courses.code as course_code',
                'student_courses.title as course_title',
                'academic_details.matric_no',
                'departments.name as department_name',
                'users.surname',
                'users.firstname',
                'users.m_name',
            ])
            ->map(static function ($row): array {
                return [
                    'id' => $row->id,
                    'course_code' => $row->course_code,
                    'course_title' => $row->course_title,
                    'matric_no' => $row->matric_no,
                    'department_name' => $row->department_name,
                    'student_name' => trim(implode(' ', array_filter([
                        $row->surname,
                        $row->firstname,
                        $row->m_name,
                    ]))),
                ];
            })
            ->all();
    }

    private function createAudit(string $actionType, array $courseCodes, int $matchedCount, int $droppedCount, array $validated): void
    {
        CourseDropAudit::query()->create([
            'admin_user_id' => Auth::id(),
            'academic_session' => $validated['academicSession'],
            'course_codes' => $courseCodes,
            'filters' => [
                'department_id' => $validated['departmentId'] ?? null,
                'programme_id' => $validated['programmeId'] ?? null,
                'student_level_id' => $validated['studentLevelId'] ?? null,
            ],
            'matched_count' => $matchedCount,
            'dropped_count' => $droppedCount,
            'action_type' => $actionType,
            'note' => $validated['note'] ?? null,
        ]);
    }

    public function render()
    {
        return view('livewire.admin.manage-course-drops', [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'programmes' => Programme::query()->orderBy('name')->get(['id', 'name']),
            'levels' => StudentLevel::query()->orderBy('level')->get(['id', 'level']),
            'audits' => CourseDropAudit::query()
                ->with('adminUser:id,firstname,surname')
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }
}
