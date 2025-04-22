<?php

use App\Http\Controllers\PrintCourseForm;
use App\Http\Controllers\PrintExamCard;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\StudentIndex;
use App\Http\Controllers\UgSchoolFeesController;

use App\Http\Livewire\Transactions\SchoolFeesInvoice;
use App\Http\Controllers\SchoolFeesTransactionController;
use App\Http\Livewire\Student\CourseRegistration;
use App\Http\Livewire\Student\ExamCard;
use App\Http\Livewire\Transactions\UtmeSchoolFeesInvoice;

Route::get('dashboard', StudentIndex::class)->name('dashboard');
Route::get('transactions/schoolfees/invoice', SchoolFeesInvoice::class)->name('school-fees-invoice');
Route::get('transactions/ug-school-fees/{user}', UtmeSchoolFeesInvoice::class)->name('ug-school-fees');
Route::get('/transactions/ug-generate-invoice/{studenttransaction}', [UgSchoolFeesController::class, 'index'])->name('ug-payment');
Route::get('/exam-card', ExamCard::class)->name('exam-card');
// courses
Route::get('course-registration', CourseRegistration::class)->name('course-registration');
Route::get('print-course-form/{user}', [PrintCourseForm::class, 'print'])->name('print-course-form');
Route::get('print-exam-card/{session}/{semester}', PrintExamCard::class)
    ->where([
        'session' => '[0-9]{4}-[0-9]{4}',
        'semester' => 'first|second'
    ])
    ->name('print-exam-card');

// Normal Controller
Route::post('/transactions/generate-invoice', [SchoolFeesTransactionController::class, 'generateInvoice'])->name('invoice');
Route::get('/transactions/generate-invoice/{studenttransaction}', [SchoolFeesTransactionController::class, 'index'])->name('payment');
Route::get('/payment/status/{rrr}', [SchoolFeesTransactionController::class, 'checkTransactionStatus'])->name('payment.status');
Route::get('/payment/ug/status/{rrr}', [UgSchoolFeesController::class, 'checkTransactionStatus'])->name('ug-payment-status');
