<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\StudentIndex;
use App\Http\Controllers\UgSchoolFeesController;

use App\Http\Livewire\Transactions\SchoolFeesInvoice;
use App\Http\Controllers\SchoolFeesTransactionController;
use App\Http\Livewire\Student\CourseRegistration;
use App\Http\Livewire\Transactions\UtmeSchoolFeesInvoice;

Route::get('dashboard', StudentIndex::class)->name('dashboard');
Route::get('transactions/schoolfees/invoice', SchoolFeesInvoice::class)->name('school-fees-invoice');
Route::get('transactions/ug-school-fees/{user}', UtmeSchoolFeesInvoice::class)->name('ug-school-fees');
Route::get('/transactions/ug-generate-invoice/{studenttransaction}', [UgSchoolFeesController::class, 'index'])->name('ug-payment');
// courses
Route::get('course-registration', CourseRegistration::class)->name('course-registration');
// Normal Controller
Route::post('/transactions/generate-invoice', [SchoolFeesTransactionController::class, 'generateInvoice'])->name('invoice');
Route::get('/transactions/generate-invoice/{studenttransaction}', [SchoolFeesTransactionController::class, 'index'])->name('payment');
Route::get('/payment/status/{rrr}', [SchoolFeesTransactionController::class, 'checkTransactionStatus'])->name('payment.status');
