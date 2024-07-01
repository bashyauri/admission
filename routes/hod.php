<?php

use App\Http\Livewire\Dashboards\HodIndex;
use App\Http\Livewire\Hod\Applicants\AllApplicants;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', HodIndex::class)->name('dashboard');
Route::get('all-applicants', AllApplicants::class)->name('all-applicants');
