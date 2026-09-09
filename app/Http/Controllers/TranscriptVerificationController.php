<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Transcript;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TranscriptVerificationController extends Controller
{
    public function __construct(
        protected GradeCalculationService $gradeCalculationService
    ) {}

    /**
     * Publicly verify authenticity of an academic transcript via QR code or reference.
     *
     * @param string $code Verification code (e.g. TRV-XXXX-XXXX-XXXX)
     * @return View
     */
    public function verify(string $code): View
    {
        $transcript = Transcript::query()
            ->with(['student.academicDetail.department', 'student.academicDetail.course'])
            ->where('verification_code', $code)
            ->first();

        if (!$transcript || !$transcript->student) {
            return view('transcripts.verify-not-found', [
                'code' => $code,
            ]);
        }

        $student        = $transcript->student;
        $academicDetail = $student->academicDetail;
        $summary        = $this->gradeCalculationService->calculateCGPA($student->id);

        return view('transcripts.verify', [
            'transcript'     => $transcript,
            'student'        => $student,
            'academicDetail' => $academicDetail,
            'summary'        => $summary,
            'verified'       => true,
            'verified_at'    => now(),
        ]);
    }
}
