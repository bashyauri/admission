<?php

use App\Http\Livewire\Dashboards\HodIndex;
use App\Http\Livewire\Hod\Applicants\AllApplicants;
use App\Http\Livewire\Hod\Applicants\ApplicantEdit;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', HodIndex::class)->name('dashboard');
Route::get('all-applicants', AllApplicants::class)->name('all-applicants');
Route::get('edit-applicant/{user}', ApplicantEdit::class)->name('edit-applicant');