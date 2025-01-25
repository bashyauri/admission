<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\CitIndex;
use App\Http\Livewire\Cit\PaidAcceptanceFees;
// use App\Http\Livewire\Dashboards\HodIndex;
// use App\Http\Livewire\Hod\Applicants\AllApplicants;
// use App\Http\Livewire\Hod\Applicants\ApplicantEdit;
// use App\Http\Livewire\Hod\Applicants\NotRecommended;
// use App\Http\Livewire\Hod\Applicants\ShortlistedApplicants;
// use App\Http\Livewire\Hod\HodProfile;
use App\Http\Controllers\SchoolFeesTransactionController;
use App\Http\Livewire\Transactions\UtmeSchoolFeesInvoice;

Route::get('dashboard', CitIndex::class)->name('dashboard');
Route::get('paid-acceptance-fees', PaidAcceptanceFees::class)->name('paid-acceptance-fees');
// Route::post('/transactions/generate-invoice', [SchoolFeesTransactionController::class, 'generateInvoice'])->name('invoice');
Route::get('transactions/utme-school-fees/{user}', UtmeSchoolFeesInvoice::class)->name('utme-school-fees');
