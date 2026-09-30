<?php

namespace App\Http\Livewire\Dashboards;

use Livewire\Component;
use App\Services\Report\ApplicantReportService;
use App\Enums\ProgrammesEnum;
use App\Models\CarryOverCourse;

class HodIndex extends Component
{
    public int $totalApplicants;
    public int $notRecommendedApplicants;
    public int $shortlistedApplicants;
    public int $paidAdmissionFees;
    public int $paidAcceptanceFees;
    public int $pendingCarryOverReviews = 0;

    public function mount(ApplicantReportService $applicantReportService)
    {
        $departmentId = auth()->user()->hodDetails?->department_id;

        $this->totalApplicants = $applicantReportService->totalApplicants($departmentId);
        $this->notRecommendedApplicants = $applicantReportService->applicantsNotRecommended($departmentId);
        $this->shortlistedApplicants = $applicantReportService->applicantsShortlisted($departmentId);
        $this->paidAdmissionFees = $applicantReportService->getPaidAdmissionFees($departmentId);
        $this->paidAcceptanceFees = $applicantReportService->getPaidAcceptanceFees($departmentId);

        $reviewQuery = CarryOverCourse::query()
            ->where('is_cleared', false)
            ->where('registration_status', 'review_required')
            ->whereHas('user', fn ($query) => $query->where('programme_id', ProgrammesEnum::Undergraduate->value));
        $user = auth()->user();
        if (!$user->canActAsAdmin() && !$user->canActAsCit()) {
            $departmentIds = array_filter([$departmentId, ...$user->capabilityDepartments('hod')]);
            $reviewQuery->whereHas('user.academicDetail', fn ($query) => $query->whereIn('department_id', $departmentIds));
        }
        $this->pendingCarryOverReviews = $reviewQuery->count();
    }

    public function render()
    {
        return view('livewire.dashboards.hod-index', [
            'totalApplicants' => $this->totalApplicants,
            'notRecommendedApplicants' => $this->notRecommendedApplicants,
            'shortlistedApplicants' => $this->shortlistedApplicants,
            'paidAdmissionFees' => $this->paidAdmissionFees,
            'paidAcceptanceFees' => $this->paidAcceptanceFees,
            'pendingCarryOverReviews' => $this->pendingCarryOverReviews,
        ]);
    }
}
