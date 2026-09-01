<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Coordinator\AddCourse;
use App\Http\Livewire\Dashboards\CoordinatorIndex;
use App\Http\Livewire\Coordinator\GenerateStudentPin;
use App\Http\Livewire\Coordinator\DepartmentLevelUnits;
use App\Http\Livewire\Coordinator\CoordinatorCourseResultReview;
use App\Http\Livewire\Coordinator\CoordinatorResultReview;

Route::get('dashboard', CoordinatorIndex::class)->name('dashboard');
Route::get('add-course', AddCourse::class)->name('add-course');
Route::get('generate-student-pin', GenerateStudentPin::class)->name('generate-student-pin');
Route::get('department-level-units', DepartmentLevelUnits::class)->name('department-level-units');
Route::get('result-review/{course}', CoordinatorCourseResultReview::class)->name('result-review.course');
Route::get('result-review', CoordinatorResultReview::class)->name('result-review');
