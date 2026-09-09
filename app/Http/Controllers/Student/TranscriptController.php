<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\TranscriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TranscriptController extends Controller
{
    public function __construct(
        protected TranscriptService $transcriptService
    ) {}

    /**
     * Download the undergraduate student's academic transcript as PDF.
     */
    public function download(Request $request): Response
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        abort_unless($student !== null, 401);

        if (!$student->isUndergraduate()) {
            abort(403, 'Academic transcripts in this portal are strictly for undergraduate students.');
        }

        $official = $request->boolean('official', true);

        return $this->transcriptService->downloadTranscript($student, $official);
    }

    /**
     * Preview/stream the undergraduate student's academic transcript in browser.
     */
    public function preview(Request $request): Response
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        abort_unless($student !== null, 401);

        if (!$student->isUndergraduate()) {
            abort(403, 'Academic transcripts in this portal are strictly for undergraduate students.');
        }

        $official = $request->boolean('official', false);

        return $this->transcriptService->streamTranscript($student, $official);
    }
}
