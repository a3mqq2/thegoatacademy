<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseScheduleController;
use App\Http\Controllers\Instructor\CoursesController;
use App\Http\Controllers\Instructor\DashboardController;
use App\Http\Controllers\Instructor\ProgressTestController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
Route::put('/profile', [DashboardController::class, 'profile_update'])->name('profile.update');

Route::get('/courses', [CoursesController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}/{CourseSchedule}/take-attenance', [CoursesController::class, 'take_attendance'])
     ->name('courses.take_attendance');

Route::post('/courses/{course}/schedules', [CourseScheduleController::class, 'store'])->name('courses.schedules.store');
Route::get('/courses/{course}/show', [CoursesController::class, 'show'])->name('courses.show');
Route::get('/courses/{course}/print', [CoursesController::class, 'print'])->name('courses.print');

// new per-student stats page
Route::get(
   '/courses/{course}/students/{student}/stats',
   [CoursesController::class, 'studentStats']
)->name('courses.students.stats');



Route::get(
    '/progress-test/{progressTest}',
    [ProgressTestController::class, 'show']
)->name('courses.progress_tests.show');


Route::get(
    '/progress-test/{progressTest}/print',
    [ProgressTestController::class, 'print']
)->name('courses.progress_tests.print');
