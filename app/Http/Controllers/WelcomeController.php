<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Programme;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Get undergraduate courses (programme_id 7 - Degree Program only)
        $undergraduateCourses = Course::with(['department', 'programme'])
            ->where('programme_id', 7)
            ->get()
            ->groupBy('department.name');

        // Get postgraduate courses (programme_id 6 - Postgraduate Diploma)
        $postgraduateCourses = Course::with(['department', 'programme'])
            ->where('programme_id', 6)
            ->get()
            ->groupBy('department.name');

        return view('welcome', compact('undergraduateCourses', 'postgraduateCourses'));
    }
}