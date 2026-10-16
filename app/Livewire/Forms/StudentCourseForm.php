<?php

namespace App\Livewire\Forms;

use App\Models\StudentCourse;
use App\Models\StudentLevel;
use Livewire\Form;

class StudentCourseForm extends Form
{
    public $code;

    public $title;
    public $units;

    public $level_id;

    public $semester;


    protected function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $normalizedCode = strtoupper(trim((string) $value));

                    if ($normalizedCode !== '' && StudentCourse::query()
                        ->whereRaw('UPPER(TRIM(code)) = ?', [$normalizedCode])
                        ->exists()) {
                        $fail('This course code already exists in the shared catalog. Use the existing course instead of adding it again.');
                    }
                },
            ],
            'title' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $normalizedTitle = mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $value)));

                    if ($normalizedTitle !== '' && StudentCourse::query()
                        ->whereRaw('LOWER(TRIM(title)) = ?', [$normalizedTitle])
                        ->exists()) {
                        $fail('This course title already exists in the shared catalog. Search for and use the existing course.');
                    }
                },
            ],
            'units' => ['required', 'integer', 'min:1'],
            'semester' => ['required', 'integer', 'in:1,2'],
            'level_id' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $isUndergraduateLevel = StudentLevel::query()
                        ->whereKey($value)
                        ->whereIn('level', ['100', '200', '300', '400', '500'])
                        ->exists();

                    if (! $isUndergraduateLevel) {
                        $fail('Select a valid undergraduate level from the course catalog.');
                    }
                },
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'code.required' => 'Enter the course code.',
            'title.required' => 'Enter the course title.',
            'units.required' => 'Enter the course credit units.',
            'units.integer' => 'Credit units must be a whole number.',
            'units.min' => 'Credit units must be at least 1.',
            'semester.required' => 'Select the course semester.',
            'semester.integer' => 'Select a valid semester.',
            'semester.in' => 'Select First or Second semester.',
            'level_id.required' => 'Select the course level.',
        ];
    }

    public function store()
    {
        $this->code = strtoupper(trim((string) $this->code));
        $this->title = preg_replace('/\s+/', ' ', trim((string) $this->title));
        $this->validate();

        StudentCourse::create([
            'code' => $this->code,
            'title' => $this->title,
            'units' => $this->units,
            'student_level_id' => (int)$this->level_id,
            'semester' => (int) $this->semester,


        ]);
    }
}
