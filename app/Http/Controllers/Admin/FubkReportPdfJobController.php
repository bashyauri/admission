<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Jobs\GenerateFubkPdfReportJob;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FubkReportPdfJobController extends Controller
{
    private const CACHE_TTL_SECONDS = 86400;

    public function queue(Request $request): View
    {
        $validated = $request->validate([
            'type' => ['required', 'in:fresh,returning,all'],
            'dataset' => ['required', 'in:biodata,course_registration'],
            'session' => ['required', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'programme_id' => ['nullable', 'integer', 'exists:programmes,id'],
            'columns' => ['nullable', 'string', 'max:2000'],
        ]);

        $token = (string) Str::uuid();
        $cacheKey = $this->cacheKey($token);

        Cache::put($cacheKey, [
            'status' => 'queued',
            'message' => 'PDF generation has started.',
            'user_id' => Auth::id(),
            'token' => $token,
            'download_path' => null,
            'filename' => null,
        ], now()->addSeconds(self::CACHE_TTL_SECONDS));

        GenerateFubkPdfReportJob::dispatch($token, [
            'type' => (string) $validated['type'],
            'dataset' => (string) $validated['dataset'],
            'session' => (string) $validated['session'],
            'search' => (string) ($validated['search'] ?? ''),
            'department_id' => isset($validated['department_id']) ? (int) $validated['department_id'] : null,
            'programme_id' => isset($validated['programme_id']) ? (int) $validated['programme_id'] : null,
            'columns' => (string) ($validated['columns'] ?? ''),
            'user_id' => Auth::id(),
        ]);

        return view('admin.report.fubk-pdf-job', [
            'token' => $token,
            'statusUrl' => route('admin.fubk.pdf.status', ['token' => $token]),
            'downloadUrl' => route('admin.fubk.pdf.download', ['token' => $token]),
        ]);
    }

    public function status(string $token): JsonResponse
    {
        $status = Cache::get($this->cacheKey($token));

        if (!is_array($status)) {
            return response()->json([
                'status' => 'missing',
                'message' => 'Report job was not found or has expired.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (($status['user_id'] ?? null) !== Auth::id()) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'You are not allowed to view this report job.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'status' => $status['status'] ?? 'queued',
            'message' => $status['message'] ?? 'Processing...',
        ]);
    }

    public function download(string $token)
    {
        $status = Cache::get($this->cacheKey($token));

        if (!is_array($status)) {
            abort(Response::HTTP_NOT_FOUND, 'Report job not found or expired.');
        }

        if (($status['user_id'] ?? null) !== Auth::id()) {
            abort(Response::HTTP_FORBIDDEN, 'You are not allowed to download this report.');
        }

        if (($status['status'] ?? null) !== 'completed') {
            abort(Response::HTTP_ACCEPTED, 'Report is still processing.');
        }

        $downloadPath = (string) ($status['download_path'] ?? '');

        if ($downloadPath === '' || !Storage::disk('local')->exists($downloadPath)) {
            abort(Response::HTTP_NOT_FOUND, 'Generated report file is no longer available.');
        }

        $filename = (string) ($status['filename'] ?? ('fubk-report-' . $token . '.pdf'));

        return response()->download(Storage::disk('local')->path($downloadPath), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function cacheKey(string $token): string
    {
        return 'fubk_pdf_job:' . $token;
    }
}
