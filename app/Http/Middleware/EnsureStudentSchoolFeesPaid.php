<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\TransactionStatus;
use App\Models\StudentTransaction;
use Illuminate\Http\Request;
use App\Services\AcademicSessionService;

class EnsureStudentSchoolFeesPaid
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $hasPaidSchoolFees = StudentTransaction::where('user_id', $user->id)
            ->whereIn('resource', [
                config('remita.schoolfees.description'),
                config('remita.schoolfees.ug_schoolfees_description'),
            ])
            ->where('status', TransactionStatus::APPROVED->value)
            ->where('acad_session', app(AcademicSessionService::class)->getAcademicSession($user))
            ->exists();

        if (!$hasPaidSchoolFees) {
            abort(403, 'You must pay school fees before accessing this page.');
        }

        return $next($request);
    }
}
