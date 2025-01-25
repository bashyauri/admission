<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\CitIndex;
use App\Http\Livewire\Cit\PaidAcceptanceFees;;

use App\Http\Controllers\SchoolFeesTransactionController;
use App\Http\Controllers\UgSchoolFeesController;
use App\Http\Livewire\Transactions\UtmeSchoolFeesInvoice;

Route::get('dashboard', CitIndex::class)->name('dashboard');
Route::get('paid-acceptance-fees', PaidAcceptanceFees::class)->name('paid-acceptance-fees');
Route::post('/transactions/generate-invoice', [UgSchoolFeesController::class, 'generateInvoice'])->name('invoice');
Route::get('transactions/utme-school-fees/{user}', UtmeSchoolFeesInvoice::class)->name('utme-school-fees');
Route::get('/transactions/generate-invoice/{studenttransaction}', [UgSchoolFeesController::class, 'index'])->name('payment');
Route::get('/payment/status/{rrr}', [UgSchoolFeesController::class, 'checkTransactionStatus'])->name('payment.status');
