<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResultTemplateExport implements FromCollection, WithHeadings
{
    protected $students;

    public function __construct($students)
    {
        $this->students = $students;
    }

    public function collection()
    {
        if ($this->students && method_exists($this->students, 'loadMissing')) {
            $this->students->loadMissing('academicDetail.user');
        }

        return $this->students->map(function ($student) {
            return [
                'Matric No' => $student->academicDetail->matric_no ?? '',
                'First Name' => $student->academicDetail->user->firstname ?? '',
                'Surname' => $student->academicDetail->user->surname ?? '',
                'CA Score' => '', // Blank for lecturer to fill
                'Exam Score' => '', // Blank for lecturer to fill
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Matric No',
            'First Name',
            'Surname',
            'CA Score (Max 40)',
            'Exam Score (Max 60)',
        ];
    }
}
