<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Report\UtmeService;
use Barryvdh\DomPDF\Facade\Pdf;


class ReportController extends Controller
{
    public function __construct(protected UtmeService $utmeService) {}
    public function exportRecommendedApplicants()
    {
        try {
            $recommendedApplicants = $this->utmeService->recommendedUTMEApplicantsDetails();
        } catch (Exception $e) {
            Log::info("Something went wrong: " . $e->getMessage());
            return redirect()->back()->with(['error_message' => 'Something went wrong. Please contact CIT.']);
        }




        $pdf = PDF::loadView(
            'admin.report.recommended-applicants-pdf',
            ['recommendedApplicants' => $recommendedApplicants]
        );
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->setPaper('A4', 'landscape');


        return $pdf->stream('recommended-candidates.pdf');
    }
}
