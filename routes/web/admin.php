<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\ExcludeReasonController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GroupTypeController;
use App\Http\Controllers\WithdrawnReasonController;
use App\Http\Controllers\Admin\CourseTypeController;
use App\Http\Controllers\Admin\CourseStudentController;
use App\Http\Controllers\Admin\QualitySettingController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::put('admin/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
Route::resource('users', UserController::class);
Route::put('course-types/{course_type}/toggle', [CourseTypeController::class, 'toggle'])->name('course-types.toggle');
Route::resource('course-types', CourseTypeController::class);
Route::put('group-types/{group_type}/toggle', [GroupTypeController::class, 'toggle'])->name('group-types.toggle');
Route::resource('group-types', GroupTypeController::class);
Route::resource('students', StudentController::class);
Route::put('students/{student}/exclude', [StudentController::class, 'exclude'])->name('students.exclude');
Route::put('students/{student}/withdraw', [StudentController::class, 'withdraw'])->name('students.withdraw');
Route::put('/courses/{id}/cancel', [CourseController::class, 'cancel'])->name('courses.cancel');
Route::resource('courses', CourseController::class);


Route::put('/courses/{course}/students/{student}/exclude', [CourseStudentController::class, 'exclude'])->name('courses.students.exclude');
Route::put('/courses/{course}/students/{student}/withdraw', [CourseStudentController::class, 'withdraw'])->name('courses.students.withdraw');


Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

Route::resource('exclude_reasons', ExcludeReasonController::class)->only(['index','create','store']);
Route::resource('withdrawn_reasons', WithdrawnReasonController::class)->only(['index','create','store']);

Route::get('/quality-settings', [QualitySettingController::class, 'index'])->name('quality-settings.index');
Route::put('/quality-settings/update', [QualitySettingController::class, 'update'])->name('quality-settings.update');
