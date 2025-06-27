<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\ExcludeReasonController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GroupTypeController;
use App\Http\Controllers\WithdrawnReasonController;
use App\Http\Controllers\Admin\CourseTypeController;
use App\Http\Controllers\Admin\StudentFileController;
use App\Http\Controllers\Instructor\CoursesController;
use App\Http\Controllers\Admin\CourseStudentController;
use App\Http\Controllers\CourseScheduleController;
use App\Http\Controllers\Admin\QualitySettingController;
// ADD THESE NEW CONTROLLER IMPORTS
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\MeetingPlatformController;
use App\Http\Controllers\AdminProgressTestController;
use App\Http\Controllers\Instructor\ProgressTestController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::put('admin/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
Route::resource('users', UserController::class);
Route::put('course-types/{course_type}/toggle', [CourseTypeController::class, 'toggle'])->name('course-types.toggle');
Route::resource('course-types', CourseTypeController::class);
Route::put('group-types/{group_type}/toggle', [GroupTypeController::class, 'toggle'])->name('group-types.toggle');
Route::resource('group-types', GroupTypeController::class);
Route::resource('students', StudentController::class);
Route::get('students/{student}/print-suggestion-courses', [StudentController::class, 'print_suggestion_courses'])->name('students.print_suggestion_courses');
Route::put('students/{student}/exclude', [StudentController::class, 'exclude'])->name('students.exclude');
Route::put('students/{student}/withdraw', [StudentController::class, 'withdraw'])->name('students.withdraw');
Route::get('/courses/{id}/restore', [CourseController::class, 'restore'])->name('courses.restore');
Route::put('/courses/{id}/cancel', [CourseController::class, 'cancel'])->name('courses.cancel');
Route::put('/courses/{course}/update-status', [CourseController::class, 'updateStatus'])->name('courses.update-status');

Route::post('/courses/{id}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
Route::get('/courses/{id}/print', [CourseController::class, 'print'])->name('courses.print');
Route::put('/courses/{id}/reactive', [CourseController::class, 'reactive'])->name('courses.reactive');
Route::get('/courses/search', [CourseController::class, 'search'])->name('courses.search');
Route::resource('courses', CourseController::class);

// ========== NEW SCHEDULE MANAGEMENT ROUTES ==========
// Schedule CRUD operations
Route::post('/courses/{course}/schedules', [CourseScheduleController::class, 'store'])->name('courses.schedules.store');
Route::put('/courses/{course}/schedules/{schedule}', [CourseScheduleController::class, 'update'])->name('courses.schedules.update');
Route::delete('/courses/{course}/schedules/{schedule}', [CourseScheduleController::class, 'destroy'])->name('courses.schedules.destroy');
// Schedule status update via AJAX
Route::put('/courses/{course}/schedules/{schedule}/status', [CourseScheduleController::class, 'updateStatus'])->name('courses.schedules.update-status');

// ========== NEW ADMIN PROGRESS TEST ROUTES ==========
// Admin Progress Test CRUD operations
Route::post('/courses/{course}/progress-tests', [AdminProgressTestController::class, 'store'])->name('courses.progress_tests.store');
Route::put('/courses/{course}/progress-tests/{progressTest}', [AdminProgressTestController::class, 'update'])->name('courses.progress_tests.update');
Route::delete('/courses/{course}/progress-tests/{progressTest}', [AdminProgressTestController::class, 'destroy'])->name('courses.progress_tests.destroy');
// Admin Progress Test with admin override capability
Route::get('/admin/progress-tests/{progressTest}', [AdminProgressTestController::class, 'show'])->name('admin.courses.progress_tests.show');
Route::put('/admin/progress-tests/{progressTest}', [AdminProgressTestController::class, 'updateGrades'])->name('admin.courses.progress_tests.update_grades');

// ========== ADMIN ATTENDANCE ROUTES ==========
// Admin attendance with override capability
Route::get('/courses/{course}/{CourseSchedule}/admin-attendance', [AdminAttendanceController::class, 'take'])->name('admin.courses.take_attendance');
Route::post('/courses/{course}/admin-attendance', [AdminAttendanceController::class, 'store'])->name('admin.courses.attendance.store');

Route::put('/courses/{course}/students/{student}/exclude', [CourseStudentController::class, 'exclude'])->name('courses.students.exclude');
Route::put('/courses/{course}/students/{student}/withdraw', [CourseStudentController::class, 'withdraw'])->name('courses.students.withdraw');

Route::get(
    '/progress-test/{progressTest}',
    [ProgressTestController::class, 'show']
)->name('courses.progress_tests.show');

Route::get(
    '/progress-test/{progressTest}/print',
    [ProgressTestController::class, 'print']
)->name('courses.progress_tests.print');

// ========== FIXED EXISTING ROUTE ==========
// Fixed typo: "attenance" -> "attendance"
Route::get('/courses/{course}/{CourseSchedule}/take-attenance', [CoursesController::class, 'take_attendance'])
     ->name('courses.take_attendance');
     
Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

Route::resource('exclude_reasons', ExcludeReasonController::class)->only(['index','create','store']);
Route::resource('withdrawn_reasons', WithdrawnReasonController::class)->only(['index','create','store']);

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('/settings/update', [SettingController::class, 'update'])->name('settings.update');

Route::get('/quality-settings', [QualitySettingController::class, 'index'])->name('quality-settings.index');
Route::put('/quality-settings/update', [QualitySettingController::class, 'update'])->name('quality-settings.update');

Route::resource('skills', SkillController::class);
Route::resource('levels', LevelController::class);

Route::post('/students/{student}/files', [StudentFileController::class, 'store'])
    ->name('students.files.store');

Route::get('/students/{student}/files/{file}/download', [StudentFileController::class, 'download'])
    ->name('students.files.download');

Route::put('/students/files/{file}', [StudentFileController::class, 'update'])
    ->name('students.files.update');

Route::delete('/students/files/{file}', [StudentFileController::class, 'destroy'])
    ->name('students.files.destroy');

Route::resource('meeting_platforms', MeetingPlatformController::class);

Route::resource('progressTests', ProgressTestController::class);