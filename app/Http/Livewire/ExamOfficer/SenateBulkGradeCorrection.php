<?php

declare(strict_types=1);

namespace App\Http\Livewire\ExamOfficer;

use App\Services\SenateGradeCorrectionService;

class SenateBulkGradeCorrection extends SenateGradeCorrection
{
    protected function requiredCapability(): string
    {
        return SenateGradeCorrectionService::BULK_CAPABILITY;
    }
}
