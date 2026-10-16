<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProgrammesEnum;
use App\Models\StudentLevel;
use Illuminate\Support\Collection;

/**
 * Resolves display labels and progression order without assuming a level ID is
 * also its ordinal. UG and PG can share a year ID while displaying it differently.
 */
class AcademicLevelService
{
    /** @return Collection<int, StudentLevel> */
    public function undergraduateLevels(): Collection
    {
        return StudentLevel::query()
            ->whereIn('level', ['100', '200', '300', '400', '500'])
            ->get(['id', 'level'])
            ->sortBy(fn (StudentLevel $level): int => (int) $level->level)
            ->values();
    }

    /** @return array<int, array{name: string, value: int}> */
    public function undergraduateLevelOptions(): array
    {
        return $this->undergraduateLevels()
            ->map(fn (StudentLevel $level): array => [
                'name' => (string) $level->level,
                'value' => (int) $level->id,
            ])
            ->all();
    }

    public function undergraduateYearForId(?int $levelId): ?int
    {
        if (!$levelId) {
            return null;
        }

        $label = trim((string) StudentLevel::query()->whereKey($levelId)->value('level'));
        if (preg_match('/^(100|200|300|400|500)$/', $label, $matches)) {
            return intdiv((int) $matches[1], 100);
        }

        return null;
    }

    public function undergraduateLevelIdForYear(int $year): ?int
    {
        if ($year < 1 || $year > 5) {
            return null;
        }

        $ids = StudentLevel::query()
            ->where('level', (string) ($year * 100))
            ->orderBy('id')
            ->limit(2)
            ->pluck('id');

        // A duplicate label is ambiguous; never choose one silently.
        return $ids->count() === 1 ? (int) $ids->first() : null;
    }

    public function displayLabel(?StudentLevel $level, ?int $programmeId): string
    {
        $label = trim((string) $level?->level);
        if ($label === '') {
            return 'N/A';
        }

        if ($programmeId === ProgrammesEnum::PG->value
            && preg_match('/^(100|200|300|400|500)$/', $label, $matches)) {
            return 'PGD ' . intdiv((int) $matches[1], 100);
        }

        return $label;
    }
}
