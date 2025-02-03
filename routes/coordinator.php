<?php

declare(strict_types=1);

use App\Http\Livewire\Coordinator\AddCourse;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\CoordinatorIndex;

Route::get('dashboard', CoordinatorIndex::class)->name('dashboard');
Route::get('add-course', AddCourse::class)->name('add-course');
