<?php

use Illuminate\Support\Facades\Route;

Route::get('dashboard', function () {
    var_dump('admin');
})->name('dashboard');
