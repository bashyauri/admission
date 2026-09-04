<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Lecturer\LecturerDashboard;
use App\Http\Livewire\Lecturer\ResultEntry;

Route::get('/dashboard', LecturerDashboard::class)->name('dashboard');
Route::get('/result-entry/{courseAllocation}', ResultEntry::class)->name('result-entry');
Route::get('/course-score-sheet/{departmentCourse}/{session}/{semester}/{level?}', [\App\Http\Controllers\Report\CourseScoreSheetController::class, 'print'])->name('course-score-sheet');

