<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class DisciplinaryActionPolicy
{
    public function manage(User $actor, User $student): bool
    {
        if (! $student->isUndergraduate()) {
            return false;
        }

        if ($actor->isAdmin() || $actor->isExamOfficer()) {
            return true;
        }

        return $actor->capabilities()
            ->where('capability', 'disciplinary_actions.manage')
            ->where(function ($query) use ($student) {
                $query->whereNull('department_id');

                if ($student->academicDetail?->department_id) {
                    $query->orWhere('department_id', $student->academicDetail->department_id);
                }
            })
            ->exists();
    }
}
