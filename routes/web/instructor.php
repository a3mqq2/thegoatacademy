<?php

use App\Http\Controllers\Instructor\CoursesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Instructor\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/courses', [CoursesController::class, 'index'])->name('courses');