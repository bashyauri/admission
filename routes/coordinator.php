<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Coordinator\AddCourse;
use App\Http\Livewire\Dashboards\CoordinatorIndex;
use App\Http\Livewire\Coordinator\GenerateStudentPin;
use App\Http\Livewire\Coordinator\DepartmentLevelUnits;

Route::get('dashboard', CoordinatorIndex::class)->name('dashboard');
Route::get('add-course', AddCourse::class)->name('add-course');
Route::get('generate-student-pin', GenerateStudentPin::class)->name('generate-student-pin');
Route::get('department-level-units', DepartmentLevelUnits::class)->name('department-level-units');
