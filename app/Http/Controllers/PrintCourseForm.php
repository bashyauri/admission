<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Enums\TransactionStatus;
use App\Models\StudentTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\CourseRegistrationService;
use App\Services\AcademicSessionService;

class PrintCourseForm extends Controller
{
    public $student;
    public function __construct(protected CourseRegistrationService $service) {}
    public function print(User $user)
    {
        $authenticatedUser = Auth::user();
        $academicSession = app(AcademicSessionService::class)->getAcademicSession($user);

        abort_unless($authenticatedUser && $authenticatedUser->id === $user->id, 403);

        $hasPaidSchoolFees = StudentTransaction::where('user_id', $authenticatedUser->id)
            ->whereIn('resource', [
                config('remita.schoolfees.description'),
                config('remita.schoolfees.ug_schoolfees_description'),
            ])
            ->where('status', TransactionStatus::APPROVED->value)
            ->where('acad_session', app(AcademicSessionService::class)->getAcademicSession($authenticatedUser))
            ->exists();

        abort_unless($hasPaidSchoolFees, 403, 'You must pay school fees before printing your course form.');

        try {
            $registeredCourses = $this->service->getRegisteredCourses(
                $user->academicDetail->id,
                $academicSession,
                'semester'
            );
            $totalUnits = $this->service->getTotalUnitsOfRegisteredCourses(
                $user->academicDetail->id,
                $academicSession,
            );
        } catch (Exception $e) {
            Log::info("Something went wrong: " . $e->getMessage());
            return redirect()->back()->with(['error_message' => 'Something went wrong. Please contact CIT.']);
        }

        return view('student.print-course-form', ['courses' => $registeredCourses, 'user' => $user, 'totalUnits' => $totalUnits, 'academicSession' => $academicSession]);
    }

    public function printSession(User $user, string $session)
    {
        $authenticatedUser = Auth::user();

        abort_unless($authenticatedUser && $authenticatedUser->id === $user->id, 403);

        $academicSession = str_replace('-', '/', $session);

        try {
            $registeredCourses = $this->service->getRegisteredCourses(
                $user->academicDetail->id,
                $academicSession,
                'semester'
            );
            $totalUnits = $this->service->getTotalUnitsOfRegisteredCourses(
                $user->academicDetail->id,
                $academicSession,
            );
        } catch (Exception $e) {
            Log::info("Something went wrong: " . $e->getMessage());
            return redirect()->back()->with(['error_message' => 'Something went wrong. Please contact CIT.']);
        }

        return view('student.print-course-form', ['courses' => $registeredCourses, 'user' => $user, 'totalUnits' => $totalUnits, 'academicSession' => $academicSession]);
    }
}
