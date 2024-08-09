<?php

use App\Http\Livewire\Dashboards\HodIndex;
use App\Http\Livewire\Hod\Applicants\AllApplicants;
use App\Http\Livewire\Hod\Applicants\ApplicantEdit;
use App\Http\Livewire\Hod\Applicants\NotRecommended;
use App\Http\Livewire\Hod\HodProfile;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', HodIndex::class)->name('dashboard');
Route::get('all-applicants', AllApplicants::class)->name('all-applicants');
Route::get('not-recommended-applicants', NotRecommended::class)->name('not-recommended-applicants');
Route::get('edit-applicant/{user}', ApplicantEdit::class)->name('edit-applicant');
Route::get('hod-profile', HodProfile::class)->name('hod-profile');