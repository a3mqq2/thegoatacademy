<?php

use App\Http\Controllers\Instructor\CoursesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Instructor\DashboardController;
use App\Http\Controllers\Instructor\ProgressTestController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/courses', [CoursesController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}/{CourseSchedule}/take-attenance', [CoursesController::class, 'take_attendance'])->name('courses.take_attendance');
Route::get('/courses/{course}/show', [CoursesController::class, 'show'])->name('courses.show');
Route::get('/courses/{course}/progress-test', [ProgressTestController::class, 'create'])->name('courses.progress_tests.create');
