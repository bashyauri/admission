<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\ExamOfficerIndex;
use App\Http\Livewire\ExamOfficer\ExamOfficerResultReview;

Route::get('/dashboard', ExamOfficerIndex::class)->name('dashboard');
Route::get('/results-review', ExamOfficerResultReview::class)->name('results-review');
