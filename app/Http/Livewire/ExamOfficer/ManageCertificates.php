<?php

declare(strict_types=1);

namespace App\Http\Livewire\ExamOfficer;

use App\Models\AcademicDetail;
use App\Models\DegreeCertificate;
use App\Models\Department;
use App\Models\GraduationList;
use App\Models\GraduationListItem;
use App\Models\User;
use App\Services\AcademicSessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCertificates extends Component
{
    use LivewireAlert;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ── Filters ──────────────────────────────────────────────────────────
    public string $selectedSession  = '';
    public string $selectedDepartment = 'all';
    public string $statusFilter     = 'all'; // all | pending | collected | printed
    public string $searchQuery      = '';
    public int    $perPage          = 15;

    // ── Dropdown data ────────────────────────────────────────────────────
    public array $availableSessions    = [];
    public array $availableDepartments = [];

    // ── Issue-certificate modal ──────────────────────────────────────────
    public bool    $showIssueModal       = false;
    public ?int    $issuingListItemId    = null;
    public string  $issueStudentName     = '';
    public string  $issueMatricNo        = '';
    public string  $issueClassOfDegree   = '';
    public string  $issueRemarks         = '';

    // ── Collection-log modal ─────────────────────────────────────────────
    public bool    $showCollectModal     = false;
    public ?int    $collectingCertId     = null;
    public string  $collectStudentName   = '';
    public string  $collectRecipientName = '';
    public string  $collectRemarks       = '';

    // ─────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user || !$user->canActAsExamOfficer()) {
            abort(403, 'Unauthorized access to Certificate Management.');
        }

        // Departments
        $this->availableDepartments = Department::orderBy('name')->get()->toArray();

        // Sessions: from existing graduation lists
        $listSessions = GraduationList::query()
            ->orderByDesc('academic_session')
            ->distinct()
            ->pluck('academic_session')
            ->filter()
            ->values()
            ->toArray();

        // Merge with AcademicSessionService default
        $service        = new AcademicSessionService();
        $defaultSession = $service->getAcademicSession($user);

        $this->availableSessions = array_values(array_unique(
            array_merge($listSessions, [$defaultSession])
        ));
        rsort($this->availableSessions);

        $this->selectedSession = $this->availableSessions[0] ?? $defaultSession;
    }

    // ── Livewire lifecycle hooks ─────────────────────────────────────────

    public function updatedSelectedSession(): void    { $this->resetPage(); }
    public function updatedSelectedDepartment(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void       { $this->resetPage(); }
    public function updatedSearchQuery(): void        { $this->resetPage(); }

    // ── Issue Certificate ────────────────────────────────────────────────

    /**
     * Open modal to issue a certificate to a graduand who has been
     * staged on the Graduation List but not yet issued a certificate.
     */
    public function openIssueModal(int $listItemId): void
    {
        $item = GraduationListItem::with(['user.academicDetail', 'academicDetail'])->find($listItemId);

        if (!$item) {
            $this->alert('error', 'Graduation list item not found.');
            return;
        }

        // Guard: do not re-issue if already exists
        $exists = DegreeCertificate::where('user_id', $item->user_id)->exists();
        if ($exists) {
            $this->alert('warning', 'A certificate has already been issued for this student.');
            return;
        }

        $this->issuingListItemId  = $listItemId;
        $this->issueStudentName   = trim(
            ($item->user?->surname ?? '')
            . ' '
            . ($item->user?->firstname ?? '')
            . ' '
            . ($item->user?->m_name ?? '')
        );
        $this->issueMatricNo      = $item->matric_no ?? $item->academicDetail?->matric_no ?? '';
        $this->issueClassOfDegree = $item->class_of_degree ?? '';
        $this->issueRemarks       = '';
        $this->showIssueModal     = true;
    }

    /**
     * Confirm and generate the certificate serial number, creating
     * the DegreeCertificate record.
     */
    public function confirmIssueCertificate(): void
    {
        $user = Auth::user();

        if (!$user || !$user->canActAsExamOfficer()) {
            $this->alert('error', 'Unauthorized.');
            return;
        }

        if (!$this->issuingListItemId) {
            return;
        }

        $item = GraduationListItem::with(['user.academicDetail', 'graduationList'])->find($this->issuingListItemId);

        if (!$item) {
            $this->alert('error', 'Graduation list item not found.');
            $this->closeIssueModal();
            return;
        }

        // Guard against duplicate
        if (DegreeCertificate::where('user_id', $item->user_id)->exists()) {
            $this->alert('warning', 'Certificate already issued for this student.');
            $this->closeIssueModal();
            return;
        }

        $session = $item->graduationList?->academic_session ?? $this->selectedSession;

        $certNumber = DegreeCertificate::generateCertificateNumber($session);

        DegreeCertificate::create([
            'user_id'            => $item->user_id,
            'academic_detail_id' => $item->academic_detail_id,
            'graduation_list_id' => $item->graduation_list_id,
            'certificate_number' => $certNumber,
            'certificate_type'   => 'bachelor',
            'class_of_degree'    => $item->class_of_degree ?? '',
            'issue_date'         => now()->toDateString(),
            'is_printed'         => false,
            'is_collected'       => false,
            'remarks'            => trim($this->issueRemarks),
        ]);

        $this->closeIssueModal();
        $this->alert(
            'success',
            "Certificate {$certNumber} issued successfully for {$this->issueStudentName}."
        );
    }

    public function closeIssueModal(): void
    {
        $this->showIssueModal     = false;
        $this->issuingListItemId  = null;
        $this->issueStudentName   = '';
        $this->issueMatricNo      = '';
        $this->issueClassOfDegree = '';
        $this->issueRemarks       = '';
    }

    /**
     * Batch issue certificates for all unissued graduands in the selected session.
     */
    public function batchIssueCertificates(): void
    {
        $user = Auth::user();
        if (!$user || !$user->canActAsExamOfficer()) {
            $this->alert('error', 'Unauthorized.');
            return;
        }

        $unissuedQuery = GraduationListItem::with(['user.academicDetail', 'graduationList'])
            ->whereDoesntHave('user', function ($q) {
                $q->whereHas('degreeCertificates');
            });

        if ($this->selectedSession) {
            $unissuedQuery->whereHas('graduationList', function ($q) {
                $q->where('academic_session', $this->selectedSession);
            });
        }

        $items = $unissuedQuery->get();

        if ($items->isEmpty()) {
            $this->alert('info', 'No unissued graduands found to generate certificates for.');
            return;
        }

        $count = 0;
        foreach ($items as $item) {
            if (DegreeCertificate::where('user_id', $item->user_id)->exists()) {
                continue;
            }

            $session = $item->graduationList?->academic_session ?? $this->selectedSession;
            $certNumber = DegreeCertificate::generateCertificateNumber($session);

            DegreeCertificate::create([
                'user_id'            => $item->user_id,
                'academic_detail_id' => $item->academic_detail_id,
                'graduation_list_id' => $item->graduation_list_id,
                'certificate_number' => $certNumber,
                'certificate_type'   => 'bachelor',
                'class_of_degree'    => $item->class_of_degree ?? '',
                'issue_date'         => now()->toDateString(),
                'is_printed'         => false,
                'is_collected'       => false,
                'remarks'            => 'Batch generated via Certificate Management',
            ]);
            $count++;
        }

        $this->alert('success', "{$count} degree certificate(s) generated successfully.");
    }

    /**
     * Toggle or mark a certificate as printed.
     */
    public function togglePrinted(int $certId): void
    {
        $user = Auth::user();
        if (!$user || !$user->canActAsExamOfficer()) {
            $this->alert('error', 'Unauthorized.');
            return;
        }

        $cert = DegreeCertificate::find($certId);
        if ($cert) {
            $cert->update(['is_printed' => !$cert->is_printed]);
            $status = $cert->is_printed ? 'printed' : 'unprinted';
            $this->alert('success', "Certificate {$cert->certificate_number} marked as {$status}.");
        }
    }

    // ── Log Collection ───────────────────────────────────────────────────

    /**
     * Open collection-logging modal for a certificate already issued.
     */
    public function openCollectModal(int $certId): void
    {
        $cert = DegreeCertificate::with('user')->find($certId);

        if (!$cert) {
            $this->alert('error', 'Certificate record not found.');
            return;
        }

        if ($cert->is_collected) {
            $this->alert('info', 'This certificate has already been marked as collected.');
            return;
        }

        $this->collectingCertId     = $certId;
        $this->collectStudentName   = trim(
            ($cert->user?->surname ?? '')
            . ' '
            . ($cert->user?->firstname ?? '')
        );
        $this->collectRecipientName = $this->collectStudentName;
        $this->collectRemarks       = '';
        $this->showCollectModal     = true;
    }

    /**
     * Log certificate collection — who collected, when, any remarks.
     */
    public function confirmLogCollection(): void
    {
        $this->validate([
            'collectRecipientName' => 'required|string|max:255',
        ], [
            'collectRecipientName.required' => 'Please enter the name of the person collecting the certificate.',
        ]);

        $officer = Auth::user();

        if (!$officer || !$officer->canActAsExamOfficer()) {
            $this->alert('error', 'Unauthorized.');
            return;
        }

        if (!$this->collectingCertId) {
            return;
        }

        $cert = DegreeCertificate::find($this->collectingCertId);

        if (!$cert) {
            $this->alert('error', 'Certificate not found.');
            $this->closeCollectModal();
            return;
        }

        $cert->update([
            'is_collected'   => true,
            'collected_at'   => now(),
            'collected_by'   => $officer->id,
            'recipient_name' => trim($this->collectRecipientName),
            'is_printed'     => true,
            'remarks'        => trim($this->collectRemarks) ?: $cert->remarks,
        ]);

        $this->closeCollectModal();
        $this->alert('success', "Collection logged for {$this->collectStudentName}.");
    }

    public function closeCollectModal(): void
    {
        $this->showCollectModal     = false;
        $this->collectingCertId     = null;
        $this->collectStudentName   = '';
        $this->collectRecipientName = '';
        $this->collectRemarks       = '';
    }

    // ── Render ───────────────────────────────────────────────────────────

    public function render(): View
    {
        $user = Auth::user();

        // ── Issued Certificates Table ─────────────────────────────────
        $certQuery = DegreeCertificate::with([
            'user.academicDetail.department',
            'academicDetail.department',
            'graduationList',
            'collectedBy',
        ]);

        // Filter by session via graduationList
        if ($this->selectedSession) {
            $certQuery->whereHas('graduationList', function ($q) {
                $q->where('academic_session', $this->selectedSession);
            });
        }

        // Department filter via academicDetail
        if ($this->selectedDepartment !== 'all') {
            $certQuery->whereHas('academicDetail', function ($q) {
                $q->where('department_id', (int) $this->selectedDepartment);
            });
        }

        // Status filter
        if ($this->statusFilter === 'collected') {
            $certQuery->where('is_collected', true);
        } elseif ($this->statusFilter === 'pending') {
            $certQuery->where('is_collected', false);
        } elseif ($this->statusFilter === 'printed') {
            $certQuery->where('is_printed', true);
        }

        // Search
        if (trim($this->searchQuery) !== '') {
            $q = trim($this->searchQuery);
            $certQuery->where(function ($query) use ($q) {
                $query->where('certificate_number', 'like', "%{$q}%")
                    ->orWhere('class_of_degree', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('surname', 'like', "%{$q}%")
                            ->orWhere('firstname', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orWhere('certificate_number', 'like', "%{$q}%");
            });
        }

        $certificates = $certQuery
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        // ── Staged Graduands Without Certificate (to issue) ───────────
        $unissuedQuery = GraduationListItem::with([
            'user.academicDetail.department',
            'academicDetail.department',
            'graduationList',
        ])
        ->whereDoesntHave('user', function ($q) {
            // Exclude students who already have a certificate
            $q->whereHas('degreeCertificates');
        });

        if ($this->selectedSession) {
            $unissuedQuery->whereHas('graduationList', function ($q) {
                $q->where('academic_session', $this->selectedSession);
            });
        }

        if ($this->selectedDepartment !== 'all') {
            $unissuedQuery->whereHas('academicDetail', function ($q) {
                $q->where('department_id', (int) $this->selectedDepartment);
            });
        }

        if (trim($this->searchQuery) !== '') {
            $q = trim($this->searchQuery);
            $unissuedQuery->where(function ($query) use ($q) {
                $query->where('matric_no', 'like', "%{$q}%")
                    ->orWhere('full_name', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($uq) use ($q) {
                        $uq->where('surname', 'like', "%{$q}%")
                            ->orWhere('firstname', 'like', "%{$q}%");
                    });
            });
        }

        // Only show unissued list when not filtering for "collected" / "printed"
        $unissuedItems = in_array($this->statusFilter, ['collected', 'printed'])
            ? collect()
            : $unissuedQuery->orderBy('created_at', 'desc')->get();

        // ── Summary Stats ─────────────────────────────────────────────
        $totalIssued    = DegreeCertificate::count();
        $totalCollected = DegreeCertificate::where('is_collected', true)->count();
        $totalPending   = DegreeCertificate::where('is_collected', false)->count();

        return view('livewire.exam-officer.manage-certificates', [
            'certificates'  => $certificates,
            'unissuedItems' => $unissuedItems,
            'totalIssued'   => $totalIssued,
            'totalCollected' => $totalCollected,
            'totalPending'  => $totalPending,
        ])->layout('layouts.app');
    }
}
