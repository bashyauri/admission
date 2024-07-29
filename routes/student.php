<?php

use App\Http\Livewire\Dashboards\StudentIndex;

use Illuminate\Support\Facades\Route;

Route::get('dashboard', StudentIndex::class)->name('dashboard');
