<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\ExamOfficerIndex;
use App\Http\Livewire\ExamOfficer\ExamOfficerResultReview;
use App\Http\Livewire\ExamOfficer\GraduationAudit;
use App\Http\Livewire\ExamOfficer\ManageCertificates;

Route::get('/dashboard', ExamOfficerIndex::class)->name('dashboard');
Route::get('/results-review', ExamOfficerResultReview::class)->name('results-review');
Route::get('/graduation-audit', GraduationAudit::class)->name('graduation-audit');
Route::get('/certificates', ManageCertificates::class)->name('certificates');
Route::get('/course-score-sheet/{departmentCourse}/{session}/{semester}/{level?}', [\App\Http\Controllers\Report\CourseScoreSheetController::class, 'print'])->name('course-score-sheet');
Route::get('/senate-broadsheet/{department}/{session}/{semester}/{level?}', [\App\Http\Controllers\Report\SenateBroadsheetController::class, 'print'])->name('senate-broadsheet');
Route::get('/senate-graduation-broadsheet/{session}/{department?}', [\App\Http\Controllers\Report\SenateGraduationBroadsheetController::class, 'print'])->name('senate-graduation-broadsheet');
Route::get('/senate-graduation-broadsheet-export/{session}/{department?}', [\App\Http\Controllers\Report\SenateGraduationBroadsheetController::class, 'exportCsv'])->name('senate-graduation-broadsheet.export');
Route::get('/cohort-progression-broadsheet/{department}/{admissionSession}', [\App\Http\Controllers\Report\CohortProgressionBroadsheetController::class, 'print'])->name('cohort-progression-broadsheet');
Route::get('/cohort-progression-broadsheet-export/{department}/{admissionSession}', [\App\Http\Controllers\Report\CohortProgressionBroadsheetController::class, 'exportCsv'])->name('cohort-progression-broadsheet.export');
Route::get('/senate-pass-list/{session}/{department?}', [\App\Http\Controllers\Report\SenatePassListController::class, 'print'])->name('senate-pass-list');
Route::get('/senate-pass-list-export/{session}/{department?}', [\App\Http\Controllers\Report\SenatePassListController::class, 'exportCsv'])->name('senate-pass-list.export');
