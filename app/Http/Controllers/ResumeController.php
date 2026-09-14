<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateResumeRequest;
use App\Services\ResumePdfService;

class ResumeController extends Controller
{
    /**
     * Forma sahifasi.
     */
    public function showForm()
    {
        return view('resume.form');
    }

    /**
     * Validatsiya, PDF yaratish va download.
     * Barcha mantıq ResumePdfService'da — controller faqat oqimni boshqaradi.
     */
    public function downloadPdf(GenerateResumeRequest $request, ResumePdfService $service)
    {
        $validated = $request->validated();

        $filename = $service->makeSafeFilename($validated['full_name']);

        return $service->generateDownload($request, $filename);
    }
}
