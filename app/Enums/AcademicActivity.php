<?php

namespace App\Enums;

enum AcademicActivity: string
{
    case COURSE_REGISTRATION = 'course_registration';
    case SCHOOL_FEES = 'school_fees';
    case EXAM_REGISTRATION = 'exam_registration';
    case RESULT_PROCESSING = 'result_processing';
    case GRADUATION = 'graduation';

    public function label(): string
    {
        return match ($this) {
            self::COURSE_REGISTRATION => 'Course Registration',
            self::SCHOOL_FEES => 'School Fees',
            self::EXAM_REGISTRATION => 'Exam Registration',
            self::RESULT_PROCESSING => 'Result Processing',
            self::GRADUATION => 'Graduation',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
