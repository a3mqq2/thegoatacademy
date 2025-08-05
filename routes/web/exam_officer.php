<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamOfficer\ExamsController;
use App\Http\Controllers\ExamOfficer\CourseController;
use App\Http\Controllers\ExamOfficer\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}/show', [CourseController::class, 'show'])->name('courses.show');
Route::get('/exams', [ExamsController::class, 'index'])->name('exams.index');
Route::get('/exams/{exam}', [ExamsController::class, 'show'])->name('exams.show');
Route::post('/exams/complete', [ExamsController::class, 'markAsComplete'])->name('exams.complete');
Route::get('/exams/{exam}/print', [ExamsController::class, 'print'])->name('exams.print');
Route::post('/exams/prepare', [ExamsController::class, 'prepareExam'])
     ->name('exams.prepare');
Route::get('/exams/{exam}/grades', [ExamsController::class, 'showRecordForm'])->name('exams.grades.record');
Route::post('/exams/{exam}/grades', [ExamsController::class, 'storeGrades'])
    ->name('exams.grades.store');

Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');
    Route::post('/update-exam-dates/{course}', [DashboardController::class, 'updateExamDates'])
        ->name('updateExamDates');

        // web.php
Route::post('/exams/update-date', [ExamsController::class, 'updateDate'])->name('exams.update_date');
Route::post('/exams/assign-examiner', [ExamsController::class, 'assignExaminer'])->name('exams.assign_examiner');
Route::post('/exams/update_time', [ExamsController::class, 'updateTime'])->name('exams.update_time');

Route::post('/exams/{exam}/students/{student}/absent', [ExamsController::class, 'markAbsent'])
    ->name('exams.students.absent');

Route::post('/exams/{exam}/students/{student}/present', [ExamsController::class, 'markPresent'])
    ->name('exams.students.present');
