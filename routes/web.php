<?php

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

Route::get('/', [ResumeController::class, 'showForm'])->name('resume.form');

Route::post('/resume/pdf', [ResumeController::class, 'downloadPdf'])
    ->middleware('throttle:10,1')
    ->name('resume.pdf');
