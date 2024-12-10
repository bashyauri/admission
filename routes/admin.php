<?php

declare(strict_types=1);

use App\Http\Livewire\Admin\Settings;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\AdminIndex;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Livewire\Admin\Applicants\AllApplicants;
use App\Http\Livewire\Admin\Applicants\NotRecommended;
use App\Http\Livewire\Admin\Applicants\UploadApplicants;
use App\Http\Livewire\Admin\Applicants\EditUtmeApplicants;
use App\Http\Livewire\Admin\Applicants\ImportedApplicants;
use App\Http\Livewire\Admin\Applicants\ShortlistedApplicants;
use App\Http\Livewire\Admin\Applicants\Utme\RecommendedApplicants;
use App\Http\Livewire\Admin\Applicants\Utme\AllApplicants as UtmeAllApplicants;

Route::get('dashboard', AdminIndex::class)->name('dashboard');
Route::get('settings', Settings::class)->name('settings');
Route::get('all-applicants', AllApplicants::class)->name('all-applicants');
Route::get('not-recommended-applicants', NotRecommended::class)->name('not-recommended-applicants');
Route::get('shortlisted-applicants', ShortlistedApplicants::class)->name('shortlisted-applicants');
Route::get('upload-applicants', UploadApplicants::class)->name('upload-applicants');
Route::get('imported-applicants', ImportedApplicants::class)->name('imported-applicants');
Route::get('edit-utme-applicant/{user}', EditUtmeApplicants::class)->name('edit-utme-applicant');

Route::get('all-utme-applicants', UtmeAllApplicants::class)->name('all-utme-applicants');
Route::get('recommended-utme-applicants', RecommendedApplicants::class)->name('recommended-utme-applicants');
Route::get('/export-recommended-pdf', [ReportController::class, 'exportRecommendedApplicants'])->name('export-recommended-pdf');
