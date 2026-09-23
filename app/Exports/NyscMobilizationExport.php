<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NyscMobilizationExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['graduands'] ?? []);
    }

    public function map($graduand): array
    {
        return [
            $graduand['matric_no'] ?? '',
            $graduand['student_name'] ?? '',
            '', // Date of Birth - would need from user profile
            '', // Gender - would need from user profile
            '', // State of Origin - would need from user profile
            '', // LGA - would need from user profile
            $graduand['programme_name'] ?? '',
            $graduand['class_of_degree'] ?? '',
            number_format((float) ($graduand['final_cgpa'] ?? 0), 2),
            $this->data['session'] ?? '',
            $graduand['department_name'] ?? '',
            $graduand['mode_of_entry'] ?? '',
            $graduand['total_units_earned'] ?? 0,
            $graduand['total_units_required'] ?? 0,
            $graduand['remarks'] ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            'MATRICULATION NUMBER',
            'FULL NAME',
            'DATE OF BIRTH',
            'GENDER',
            'STATE OF ORIGIN',
            'LGA',
            'COURSE OF STUDY',
            'CLASS OF DEGREE',
            'FINAL CGPA',
            'GRADUATION SESSION',
            'DEPARTMENT',
            'MODE OF ENTRY',
            'TOTAL UNITS EARNED',
            'TOTAL UNITS REQUIRED',
            'REMARKS',
        ];
    }
}