<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class StudentStatusPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isAdmin()
            || $actor->capabilities()->whereIn('capability', [
                'student_status.view',
                'student_status.record_senate_approved_withdrawal',
            ])->exists();
    }

    public function view(User $actor, User $student): bool
    {
        return $actor->isAdmin()
            || (string) $actor->id === (string) $student->id
            || $this->hasCapabilityForStudent($actor, 'student_status.view', $student)
            || $this->hasCapabilityForStudent($actor, 'student_status.record_senate_approved_withdrawal', $student);
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

    public function recordSenateApprovedWithdrawal(User $actor, User $student): bool
    {
        return $student->isUndergraduate()
            && ($actor->isAdmin() || $this->hasCapabilityForStudent($actor, 'student_status.record_senate_approved_withdrawal', $student));
    }

    public function processVoluntary(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.process_voluntary');
    }

    public function processMedical(User $actor, User $student): bool
    {
        return $this->canManageUndergraduateStatus($actor, $student, 'student_status.process_medical');
    }

    public function processDisciplinary(User $actor, User $student): bool
    {
        return $student->isUndergraduate()
            && ($actor->isAdmin() || $actor->can('disciplinary-actions.manage', $student));
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
            && ($actor->isAdmin()
                || $this->hasCapabilityForStudent($actor, $capability, $student)
                || $this->hasCapabilityForStudent($actor, 'student_status.record_senate_approved_withdrawal', $student));
    }

    private function hasCapabilityForStudent(User $actor, string $capability, User $student): bool
    {
        $departmentId = $student->academicDetail()->value('department_id');

        return $actor->capabilities()
            ->where('capability', $capability)
            ->where(function ($query) use ($departmentId) {
                $query->whereNull('department_id');

                if ($departmentId !== null) {
                    $query->orWhere('department_id', $departmentId);
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
