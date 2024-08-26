<?php

use App\Http\Controllers\SchoolFeesTransactionController;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\StudentIndex;

use App\Http\Livewire\Transactions\SchoolFeesInvoice;

Route::get('dashboard', StudentIndex::class)->name('dashboard');
Route::get('transactions/schoolfees/invoice', SchoolFeesInvoice::class)->name('school-fees-invoice');
// Normal Controller
Route::post('/transactions/generate-invoice', [SchoolFeesTransactionController::class, 'generateInvoice'])->name('invoice');
Route::get('/transactions/generate-invoice/{studenttransaction}', [SchoolFeesTransactionController::class, 'index'])->name('payment');
Route::get('/payment/status/{rrr}', [SchoolFeesTransactionController::class, 'checkTransactionStatus'])->name('payment.status');
