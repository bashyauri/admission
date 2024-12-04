<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Models\PostUtmeUpload;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class UtmeService.
 */
class UtmeService
{
    public function getAllImportedApplicants(): int
    {
        return PostUtmeUpload::count();
    }
    public function getImportedApplicants(): Collection
    {
        return PostUtmeUpload::query()->get(["jamb_no", "name", "course", "jamb_score", "created_at", "updated_at"]);
    }
}
