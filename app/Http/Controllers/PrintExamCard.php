<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enums\TransactionStatus;
use App\Models\StudentTransaction;
use App\Services\CourseRegistrationService;
use App\Services\AcademicSessionService;

class PrintExamCard extends Controller
{
    public $student;


    public function __construct(protected CourseRegistrationService $service) {}
    public function __invoke(Request $request, $session, $semester)
    {

        $student = $request->user();

        $hasPaidSchoolFees = StudentTransaction::where('user_id', $student->id)
            ->whereIn('resource', [
                config('remita.schoolfees.description'),
                config('remita.schoolfees.ug_schoolfees_description'),
            ])
            ->where('status', TransactionStatus::APPROVED->value)
            ->where('acad_session', app(AcademicSessionService::class)->getAcademicSession($student))
            ->exists();

        abort_unless($hasPaidSchoolFees, 403, 'You must pay school fees before printing your exam card.');

        $session = str_replace('-', '/', $session);

        $semester = $semester == 'first' ? '1' : '2';

        $courses = $this->service->getRegisteredCoursesBySemester($student->academicDetail->id, $session, $semester);

        return view('student.print-exam-card', [
            'courses' => $courses,
            'student' => $student,
            'session' => $session,
            'academicSession' => $session,
            'semester' => $semester,
        ]);
    }
}
