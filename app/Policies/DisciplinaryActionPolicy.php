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

        if ($actor->isAdmin()) {
            return true;
        }

        $departmentId = $student->academicDetail()->value('department_id');

        return $actor->capabilities()
            ->where('capability', 'disciplinary_actions.manage')
            ->where(function ($query) use ($departmentId) {
                $query->whereNull('department_id');

                if ($departmentId !== null) {
                    $query->orWhere('department_id', $departmentId);
                }
            })
            ->exists();
    }
}
