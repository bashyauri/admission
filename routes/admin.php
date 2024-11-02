<?php

use App\Http\Livewire\Admin\Applicants\AllApplicants;
use App\Http\Livewire\Admin\Applicants\NotRecommended;
use App\Http\Livewire\Admin\Settings;
use App\Http\Livewire\Admin\Applicants\ShortlistedApplicants;
use App\Http\Livewire\Dashboards\AdminIndex;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', AdminIndex::class)->name('dashboard');
Route::get('settings', Settings::class)->name('settings');
Route::get('all-applicants', AllApplicants::class)->name('all-applicants');
Route::get('not-recommended-applicants', NotRecommended::class)->name('not-recommended-applicants');
Route::get('shortlisted-applicants', ShortlistedApplicants::class)->name('shortlisted-applicants');
