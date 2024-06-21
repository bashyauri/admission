<?php

use App\Http\Livewire\Dashboards\HodIndex;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', HodIndex::class)->name('dashboard');