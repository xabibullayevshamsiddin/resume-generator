<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateResumeRequest;
use App\Services\ResumePdfService;

/**
 * Mobil ilova (Flutter) uchun API.
 *
 * Muhim: validatsiya va PDF yaratish mantig'i QAYTA ISHLATILADI
 * (GenerateResumeRequest + ResumePdfService) — ikkinchi marta yozilmagan.
 * Multipart maydon formati web formasi bilan bir xil:
 *   employment[0][period], relatives[0][full_name], photo (fayl) va h.k.
 */
class ResumeApiController extends Controller
{
    /**
     * Validatsiya + PDF generatsiya — binary PDF qaytaradi.
     *
     * Validatsiya xatolarida Laravel `api` middleware guruhi 422 JSON
     * qaytaradi (errors obyekti bilan) — qo'shimcha sozlash shart emas.
     */
    public function generatePdf(GenerateResumeRequest $request, ResumePdfService $service)
    {
        $validated = $request->validated();
        $photo = $request->file('photo');
        $base = $service->makeSafeBase($validated['full_name']);

        $pdf = $service->generatePdfBinary($validated, $photo);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$base.'-malumotnoma.pdf"',
        ]);
    }
}
