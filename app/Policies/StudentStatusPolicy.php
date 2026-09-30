<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class StudentStatusPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin()
            || $actor->capabilities()->where('capability', 'student_status.view')->exists();
    }

    public function view(User $actor, User $student): bool
    {
        return $actor->isAdmin()
            || (string) $actor->id === (string) $student->id
            || $this->hasCapabilityForStudent($actor, 'student_status.view', $student);
    }

    public function viewAudit(User $actor): bool
    {
        return $actor->isAdmin() || $this->hasGlobalCapability($actor, 'student_status.audit.view');
    }

    public function recommend(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.recommend');
    }

    public function submitForSenate(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.submit_to_senate');
    }

    public function decideSenate(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.senate_decide');
    }

    public function processVoluntary(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.process_voluntary');
    }

    public function processMedical(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.process_medical');
    }

    public function requestReinstatement(User $actor, User $student): bool
    {
        return $student->isUndergraduate()
            && ((string) $actor->id === (string) $student->id
                || $actor->isAdmin()
                || $this->hasCapabilityForStudent($actor, 'student_status.request_reinstatement', $student));
    }

    public function reviewDepartmentReinstatement(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.reinstatement.department_review');
    }

    public function reviewFacultyReinstatement(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.reinstatement.faculty_review');
    }

    private function canManageUndergraduateStatus(User $actor, User $student, string $capability): bool
    {
        return $student->isUndergraduate()
            && ($actor->isAdmin() || $this->hasCapabilityForStudent($actor, $capability, $student));
    }

    private function hasCapabilityForStudent(User $actor, string $capability, User $student): bool
    {
        return $actor->capabilities()
            ->where('capability', $capability)
            ->where(function ($query) use ($student) {
                $query->whereNull('department_id');
                if ($student->academicDetail?->department_id) {
                    $query->orWhere('department_id', $student->academicDetail->department_id);
                }
            })
            ->exists();
    }

    private function hasGlobalCapability(User $actor, string $capability): bool
    {
        return $actor->capabilities()
            ->where('capability', $capability)
            ->whereNull('department_id')
            ->exists();
    }
}
