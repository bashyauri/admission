<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\AcademicDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\CourseRegistrationService;

class PrintCourseForm extends Controller
{
    public $student;
    public function __construct(protected CourseRegistrationService $service) {}
    public function print(User $user)
    {

        try {
            $registeredCourses = $this->service->getRegisteredCourses(
                $user->academicDetail->id,
                config('remita.settings.academic_session'),
                'semester'
            );
            $totalUnits = $this->service->getTotalUnitsOfRegisteredCourses(
                $user->academicDetail->id,
                config('remita.settings.academic_session'),
            );
        } catch (Exception $e) {
            Log::info("Something went wrong: " . $e->getMessage());
            return redirect()->back()->with(['error_message' => 'Something went wrong. Please contact CIT.']);
        }

        return view('student.print-course-form', ['courses' => $registeredCourses, 'user' => $user, 'totalUnits' => $totalUnits]);


        // $pdf = PDF::loadView(
        //     'student.print-course-form',
        //     ['courses' => $registeredCourses]
        // );
        // $pdf->getDomPDF()->set_option("enable_php", true);
        // $pdf->getDomPDF()->set_option('isPhpEnabled', true);


        // return $pdf->stream('course-form.pdf');
    }
}
