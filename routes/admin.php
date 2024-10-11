<?php

use App\Http\Livewire\Admin\Settings;
use App\Http\Livewire\Dashboards\AdminIndex;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', AdminIndex::class)->name('dashboard');
Route::get('settings', Settings::class)->name('settings');