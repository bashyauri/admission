<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ResultTemplateExport implements FromCollection, WithHeadings
{
    protected $students;
    protected int $maxCa;
    protected int $maxExam;

    public function __construct($students, int $maxCa = 40, int $maxExam = 60)
    {
        $this->students = $students;
        $this->maxCa = $maxCa;
        $this->maxExam = $maxExam;
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
                'Absent' => 'No',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Matric No',
            'First Name',
            'Surname',
            "CA Score (Max {$this->maxCa})",
            "Exam Score (Max {$this->maxExam})",
            'Absent (Yes/No)',
        ];
    }
}
