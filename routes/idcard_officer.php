<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Staff\IdCardProcessing;

Route::get('/processing', IdCardProcessing::class)->name('processing');
