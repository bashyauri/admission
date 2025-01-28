<?php

declare(strict_types=1);

use App\Http\Livewire\Admin\Settings;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Cit\AddMatricNo;
use App\Http\Livewire\Cit\FirstSchoolFees;
use App\Http\Livewire\Dashboards\AdminIndex;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\UgSchoolFeesController;
use App\Http\Livewire\Admin\Applicants\AllApplicants;
use App\Http\Livewire\Admin\Applicants\NotRecommended;
use App\Http\Livewire\Admin\Applicants\UploadApplicants;
use App\Http\Livewire\Admin\Applicants\EditUtmeApplicants;
use App\Http\Livewire\Admin\Applicants\ImportedApplicants;
use App\Http\Livewire\Admin\Applicants\ShortlistedApplicants;
use App\Http\Livewire\Admin\Applicants\Utme\RecommendedApplicants;
use App\Http\Livewire\Admin\Applicants\Utme\AllApplicants as UtmeAllApplicants;
use App\Http\Livewire\Admin\Applicants\Utme\ShortlistedApplicants as UtmeShortlistedApplicants;

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
Route::get('shortlisted-utme-applicants', UtmeShortlistedApplicants::class)->name('shortlisted-utme-applicants');
Route::get('/export-recommended-pdf', [ReportController::class, 'exportRecommendedApplicants'])->name('export-recommended-pdf');
Route::get('/payment/status/{rrr}', [UgSchoolFeesController::class, 'checkTransactionStatus'])->name('payment.status');
Route::get('first-school-fees', FirstSchoolFees::class)->name('first-school-fees');
Route::get('add-matric-number/{user}', AddMatricNo::class)->name('add-matric-number');
