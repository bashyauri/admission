<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use Illuminate\Database\Eloquent\Collection;

class DeMatricNumberReviewService
{
    public function getInvalidDeRecords(): Collection
    {
        return AcademicDetail::query()
            ->with(['user.proposedCourse', 'user.postUtmeUpload', 'department'])
            ->whereHas('user', fn ($query) => $query->where('programme_id', ProgrammesEnum::Undergraduate->value))
            ->whereNotNull('matric_no')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(function (AcademicDetail $academicDetail): bool {
                $suggested = $this->getSuggestedMatricNumber($academicDetail);

                return $suggested !== null && $academicDetail->matric_no !== $suggested;
            })
            ->values();
    }

    public function getSuggestedMatricNumber(AcademicDetail $academicDetail, ?string $session = null): ?string
    {
        $academicDetail->loadMissing(['user.proposedCourse', 'user.postUtmeUpload', 'department']);

        if (! $this->isDeStudent($academicDetail)) {
            return null;
        }

        if (! $academicDetail->department_id || ! $academicDetail->department) {
            return null;
        }

        $currentSession = $session ?? $academicDetail->admission_session ?? $academicDetail->acad_session;

        if (! is_string($currentSession) || trim($currentSession) === '') {
            return null;
        }

        $sequence = $this->sequencePositionFor($academicDetail, $currentSession);

        if ($sequence === null) {
            return null;
        }

        $year = $this->extractSessionStartYear($currentSession);

        if ($year === null) {
            return null;
        }

        $yearCode = substr((string) $year, 2, 2);
        $departmentCode = trim((string) ($academicDetail->department->code ?? ''));

        if ($departmentCode === '') {
            return null;
        }

        $serialNumber = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        // DE matric numbers use the 20 mode-of-entry code: 2026/2027 => 2620.
        return "{$yearCode}20{$this->facultyCode()}{$departmentCode}{$serialNumber}";
    }

    public function updateMatricNumber(AcademicDetail $academicDetail, ?string $session = null): bool
    {
        $academicDetail->loadMissing(['user.proposedCourse', 'user.postUtmeUpload']);

        if (! $this->isDeStudent($academicDetail)) {
            return false;
        }

        $effectiveSession = $session ?? $academicDetail->admission_session ?? $academicDetail->acad_session;
        if (! is_string($effectiveSession) || trim($effectiveSession) === '') {
            return false;
        }

        if (($academicDetail->admission_session ?? $academicDetail->acad_session) !== $effectiveSession) {
            return false;
        }

        $suggested = $this->getSuggestedMatricNumber($academicDetail, $effectiveSession);

        if ($suggested === null || $academicDetail->matric_no === $suggested) {
            return false;
        }

        $academicDetail->update([
            'matric_no' => $suggested,
        ]);

        return true;
    }

    private function facultyCode(): string
    {
        return '90';
    }

    private function sequencePositionFor(AcademicDetail $academicDetail, string $session): ?int
    {
        $records = AcademicDetail::query()
            ->with(['user', 'department'])
            ->where('department_id', $academicDetail->department_id)
            ->whereHas('user', fn ($query) => $query->where('programme_id', ProgrammesEnum::Undergraduate->value))
            ->where(function ($query) use ($session): void {
                $query->where('admission_session', $session)
                    ->orWhere(function ($query) use ($session): void {
                        $query->whereNull('admission_session')->where('acad_session', $session);
                    });
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($records as $position => $record) {
            if ((int) $record->id === (int) $academicDetail->id) {
                return $position + 1;
            }
        }

        return null;
    }

    private function extractSessionStartYear(string $session): ?int
    {
        $session = trim($session);

        if (preg_match('/^(\d{4})\/(\d{4})$/', $session, $matches) === 1) {
            return (int) $matches[1];
        }

        if (preg_match('/^(\d{2,4})$/', $session, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    private function isDeStudent(AcademicDetail $academicDetail): bool
    {
        $user = $academicDetail->user;

        if (! $user?->isUndergraduate()) {
            return false;
        }

        $proposedCourse = $user->proposedCourse;
        if (array_key_exists('jamb_score', $proposedCourse->getAttributes())) {
            $score = $proposedCourse->getAttribute('jamb_score');

            return $score === null || (float) $score === 0.0;
        }

        $postUtmeUpload = $user->postUtmeUpload;
        if ($postUtmeUpload) {
            return $postUtmeUpload->jamb_score === null || (float) $postUtmeUpload->jamb_score === 0.0;
        }

        return blank($user->jamb_no);
    }
}
