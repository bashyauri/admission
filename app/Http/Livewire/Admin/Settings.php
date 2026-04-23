<?php

declare(strict_types=1);

namespace App\Http\Livewire\Admin;

use App\Enums\Role;
use App\Enums\StudentLevel;
use App\Livewire\Forms\CreateCoordinatorForm;
use Livewire\Component;
use App\Models\Department;
use App\Livewire\Forms\CreateUserForm;
// use App\Livewire\Forms\StudentCourseForm;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Settings extends Component
{
    // Impersonation properties
    public $impersonate_role = '';
    public $impersonate_programme = '';
    // Properties for student recovery and password reset
    public string $recoveryPhone = '';
    public ?User $recoveredStudent = null;
    public string $newStudentPassword = '';
    use LivewireAlert;
    public CreateUserForm $form;
    public CreateCoordinatorForm $coordinatorForm;

    // Academic session switching properties
    public $admin_session;
    public $hod_session;
    public $student_session;
    public $ug_applicant_session;
    public $pg_applicant_session;
    public $sessions = [];

    public function mount()
    {
        // Example: generate sessions from 2020/2021 to 2030/2031
        $startYear = 2020;
        $endYear = 2031;
        $sessions = [];
        for ($y = $startYear; $y < $endYear; $y++) {
            $sessions[] = $y . '/' . ($y + 1);
        }
        // Load current values from config
        $this->admin_session = config('remita.settings.admin_academic_session') ?? config('remita.settings.academic_session');
        $this->hod_session = config('remita.settings.pg_academic_session');
        $this->student_session = config('remita.settings.academic_session');
        $this->ug_applicant_session = config('remita.settings.academic_session');
        $this->pg_applicant_session = config('remita.settings.pg_academic_session');

        // Sort sessions so current is first for each dropdown

        $this->sessions = $this->sortSessions($sessions, $this->admin_session, $this->hod_session, $this->student_session, $this->ug_applicant_session, $this->pg_applicant_session);

        // Load impersonation context if set
        $user = Auth::user();
        if ($user) {
            $this->impersonate_role = $user->impersonateRole ?? '';
            $this->impersonate_programme = $user->impersonateProgramme ?? '';
        }
    }

    /**
     * Sort sessions so the current session is first for each dropdown.
     */
    protected function sortSessions($sessions, $admin, $hod, $student, $ug, $pg)
    {
        // Only sort for the main dropdown, not for each field, so just use admin as primary
        $unique = array_unique(array_merge(
            array_filter([$admin, $hod, $student, $ug, $pg]),
            $sessions
        ));
        return $unique;
    }
    /**
     * Impersonate as selected role/programme
     */
    public function impersonate()
    {
        $user = Auth::user();
        if ($user) {
            $role = $this->impersonate_role ?: null;
            $programme = $this->impersonate_programme ?: null;
            $user->impersonateAs($role, $programme ? (int)$programme : null);
            session()->flash('impersonation_success', 'Impersonation context set!');
        }
    }

    /**
     * Clear impersonation context
     */
    public function clearImpersonation()
    {
        $user = Auth::user();
        if ($user) {
            $user->clearImpersonation();
            $this->impersonate_role = '';
            $this->impersonate_programme = '';
            session()->flash('impersonation_success', 'Impersonation cleared!');
        }
    }

    public function updateSessions()
    {
        // Resort sessions so the selected session is first after update
        $this->sessions = $this->sortSessions($this->sessions, $this->admin_session, $this->hod_session, $this->student_session, $this->ug_applicant_session, $this->pg_applicant_session);
        // Save to .env
        $this->setEnvValue('ADMIN_ACADEMIC_SESSION', $this->admin_session);
        $this->setEnvValue('HOD_ACADEMIC_SESSION', $this->hod_session);
        $this->setEnvValue('ACADEMIC_SESSION', $this->student_session);
        $this->setEnvValue('UG_APPLICANT_SESSION', $this->ug_applicant_session);
        $this->setEnvValue('PG_ACADEMIC_SESSION', $this->pg_applicant_session);

        // Save to settings table for dynamic updates
        foreach ([
            'ADMIN_ACADEMIC_SESSION' => $this->admin_session,
            'HOD_ACADEMIC_SESSION' => $this->hod_session,
            'ACADEMIC_SESSION' => $this->student_session,
            'UG_APPLICANT_SESSION' => $this->ug_applicant_session,
            'PG_ACADEMIC_SESSION' => $this->pg_applicant_session,
        ] as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now()]);
        }

        // Clear config cache if needed
        if (function_exists('artisan')) {
            Artisan::call('config:clear');
        }

        // Always show success message
        session()->flash('session_update_success', 'Academic sessions updated successfully!');
        $this->dispatch('session-update-success');
    }

    // Helper to update .env value
    protected function setEnvValue($key, $value)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) return;
        $env = file_get_contents($envPath);
        $pattern = "/^{$key}=.*$/m";
        $replacement = $key . '="' . $value . '"';
        if (preg_match($pattern, $env)) {
            $env = preg_replace($pattern, $replacement, $env);
        } else {
            $env .= "\n{$replacement}";
        }
        file_put_contents($envPath, $env);
    }

    public function createCourse(): void
    {
        try {
            $this->courseForm->store();
            $this->showSuccessAlert('Course Created');
            $this->courseForm->reset();
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->showValidationErrors($e);
        } catch (\Exception $e) {
            report($e);
            $this->showErrorAlert('Save failed.');
        }
    }

    private function showSuccessAlert($message): void
    {
        $this->alert('success', $message, [
            'position' => 'center',
            'timer' => 1000,
            'toast' => true,
        ]);
    }

    public function showValidationErrors(ValidationException $e): void
    {
        $this->alert('error', 'Validation Error', [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
            'text' => $e->getMessage(),
        ]);
    }

    private function showErrorAlert($message): void
    {
        $this->alert('error', $message, [
            'position' => 'center',
            'timer' => 3000,
            'toast' => true,
        ]);
    }

    public function findStudent(): void
    {
        $this->validate(['recoveryPhone' => 'required|min:6']);
        $this->recoveredStudent = User::query()
            ->whereIn('role', [Role::APPLICANT->value, Role::STUDENT->value])
            ->where('phone', $this->recoveryPhone)
            ->first();
        if (!$this->recoveredStudent) {
            $this->addError('recoveryPhone', 'No student or applicant found with this phone number.');
        }
    }

    public function resetStudentPassword(): void
    {
        $this->validate(['newStudentPassword' => 'required|min:6']);
        $this->recoveredStudent->update([
            'password' => Hash::make($this->newStudentPassword),
            'vpassword' => $this->newStudentPassword,
        ]);
        $this->newStudentPassword = '';
        $this->showSuccessAlert('Password reset successfully.');
    }

    public function clearRecovery(): void
    {
        $this->recoveryPhone = '';
        $this->recoveredStudent = null;
        $this->newStudentPassword = '';
    }

    public function render()
    {
        return view('livewire.admin.settings', [
            'departments' => Department::get(['id', 'name']),
            'roles' => Role::getRoles(),
            'levels' => StudentLevel::getLevels(),
            'sessions' => $this->sessions,
            'programmes' => [
                ['name' => 'HND', 'value' => 1],
                ['name' => 'ND', 'value' => 2],
                ['name' => 'NDS', 'value' => 3],
                ['name' => 'NCE', 'value' => 4],
                ['name' => 'PD', 'value' => 5],
                ['name' => 'PG', 'value' => 6],
                ['name' => 'Undergraduate', 'value' => 7],
            ],
        ]);
    }
}
