<?php

use App\Http\Controllers\Api\ResumeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Mobil ilova (Flutter) uchun REST API.
|
| XAVFSIZLIK: saqlangan ma'lumotnomalar ro'yxatini ochiq endpoint orqali
| HECH QACHON qaytarmang — pasport/telefon/manzillar maxfiy. Agar
| "mening ma'lumotnomalarim" funksiyasi kerak bo'lsa — sanctum token
| auth bilan alohida bosqichda qo'shiladi.
|
*/

Route::prefix('v1')->group(function () {
    Route::post('/resume/pdf', [ResumeApiController::class, 'generatePdf'])
        ->middleware('throttle:10,1')
        ->name('api.resume.pdf');
});
