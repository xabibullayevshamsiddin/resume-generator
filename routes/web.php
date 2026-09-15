<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Landing sahifa
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/yarat', [ResumeController::class, 'showForm'])->name('resume.form');

Route::post('/resume/pdf', [ResumeController::class, 'downloadPdf'])
    ->middleware('throttle:10,1')
    ->name('resume.pdf');

// Saqlangan ma'lumotnomalar
Route::get('/resumes', [ResumeController::class, 'index'])->name('resume.index');
Route::get('/resumes/{resume}', [ResumeController::class, 'show'])->name('resume.show');
Route::get('/resumes/{resume}/pdf', [ResumeController::class, 'regeneratePdf'])
    ->middleware('throttle:10,1')
    ->name('resume.regenerate');
Route::delete('/resumes/{resume}', [ResumeController::class, 'destroy'])->name('resume.destroy');
