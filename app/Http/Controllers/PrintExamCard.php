<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CourseRegistrationService;

class PrintExamCard extends Controller
{
    public $student;


    public function __construct(protected CourseRegistrationService $service) {}
    public function __invoke(Request $request, $session, $semester)
    {

        $student = $request->user();
        $session = str_replace('-', '/', $session);

        $semester = $semester == 'first' ? '1' : '2';

        $courses = $this->service->getRegisteredCoursesBySemester($student->academicDetail->id, $session, $semester);

        return view('student.print-exam-card', [
            'courses' => $courses,
            'student' => $student,
            'session' => $session,
            'semester' => $semester,
        ]);
    }
}
