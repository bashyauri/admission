<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\LecturerIndex;

Route::get('/dashboard', LecturerIndex::class)->name('dashboard');
