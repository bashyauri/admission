<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\ExamOfficerIndex;

Route::get('/dashboard', ExamOfficerIndex::class)->name('dashboard');
