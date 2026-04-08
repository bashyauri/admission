<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Staff\IdCardProcessing;
use App\Http\Controllers\PrintIdCard;

Route::get('/processing', IdCardProcessing::class)->name('processing');
Route::get('/print/{user}', PrintIdCard::class)->name('print');
