<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\ExamOfficerIndex;
use App\Http\Livewire\ExamOfficer\ExamOfficerResultReview;

Route::get('/dashboard', ExamOfficerIndex::class)->name('dashboard');
Route::get('/results-review', ExamOfficerResultReview::class)->name('results-review');
Route::get('/course-score-sheet/{departmentCourse}/{session}/{semester}/{level?}', [\App\Http\Controllers\Report\CourseScoreSheetController::class, 'print'])->name('course-score-sheet');
Route::get('/senate-broadsheet/{department}/{session}/{semester}/{level?}', [\App\Http\Controllers\Report\SenateBroadsheetController::class, 'print'])->name('senate-broadsheet');


