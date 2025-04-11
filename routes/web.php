<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\Admin\CourseController;

Route::redirect('/', '/login');
Route::get('/login', [AdminController::class, 'login'])->name('login');
Route::post('/do-login', [AdminController::class, 'do_login'])->name('do_login');
Route::get('/logout', [AdminController::class, 'logout'])->name('logout');


Route::get('/courses-suggestions', [CourseController::class, 'courses_suggestions'])->name('courses.suggestions');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/sections', function () {
        return view('sections');
    })->name('sections');

    Route::group(['middleware' => ['role:Admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
        require base_path('routes/web/admin.php');
    });

    Route::group(['middleware' => ['role:Instructor'], 'prefix' => 'instructor', 'as' => 'instructor.'], function () {
        require base_path('routes/web/instructor.php');
    });

    Route::group(['middleware' => ['role:Examiner'], 'prefix' => 'exam_officer', 'as' => 'exam_officer.'], function () {
         require base_path('routes/web/exam_officer.php');
   });

   Route::group(['middleware' => ['role:Supervisor'], 'prefix' => 'supervisor', 'as' => 'supervisor.'], function () {
         require base_path('routes/web/supervisor.php');
   });

});



Route::post('/upload-file', [FileUploadController::class, 'upload'])->name('upload.file');
Route::delete('/upload-file', [FileUploadController::class, 'revert'])->name('upload.file.revert');