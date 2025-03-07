<?php

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// sanctum group
Route::middleware(['auth:sanctum'])->group(function () {
   Route::get('course-requirements', [CourseController::class, 'courseRequirements']);
   Route::post('/students', [StudentController::class, 'store']);
   Route::post('/courses', [CourseController::class, 'store']);
   Route::put('/courses/{course}', [CourseController::class, 'update']);
});