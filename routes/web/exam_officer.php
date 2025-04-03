<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExamOfficer\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/update-exam-dates/{course}', [DashboardController::class, 'updateExamDates'])
        ->name('updateExamDates');