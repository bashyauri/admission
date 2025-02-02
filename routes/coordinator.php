<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboards\CoordinatorIndex;

Route::get('dashboard', CoordinatorIndex::class)->name('dashboard');