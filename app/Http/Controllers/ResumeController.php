<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateResumeRequest;
use App\Models\Resume;
use App\Services\ResumeDocxService;
use App\Services\ResumePdfService;
use Illuminate\Http\Request;

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
     * Validatsiya, hujjat yaratish va download (PDF yoki DOCX).
     * Barcha mantıq Service'da — controller faqat oqimni boshqaradi.
     */
    public function downloadPdf(GenerateResumeRequest $request, ResumePdfService $service, ResumeDocxService $docxService)
    {
        $validated = $request->validated();
        $format = $request->input('format') === 'docx' ? 'docx' : 'pdf';

        $photo = $request->file('photo');

        // Agar foydalanuvchi "saqlash"ni tanlagan bo'lsa — bazaga yozamiz
        if ($request->boolean('save_record')) {
            $resume = $service->storeResume($validated, $photo);

            // Hujjatni saqlangan rasm bilan yaratamiz
            return $this->downloadFromRecord($resume, $service, $docxService, $format);
        }

        $base = $service->makeSafeBase($validated['full_name']);

        if ($format === 'docx') {
            // Vaqtinchalik rasm: yaratishdan keyin (xatolikda ham) o'chiriladi
            $photoPath = $service->storeTemporaryPhoto($photo);

            try {
                $data = $service->buildViewData($validated, $photoPath);

                return $docxService->generateFromViewData($data, $photoPath, $base.'-malumotnoma.docx');
            } finally {
                $service->deleteTemporaryPhoto($photoPath);
            }
        }

        return $service->generateDownload($request, $base.'-malumotnoma.pdf');
    }

    /**
     * Saqlangan ma'lumotnomalar ro'yxati.
     */
    public function index()
    {
        $resumes = Resume::query()
            ->latest()
            ->paginate(15);

        return view('resume.index', compact('resumes'));
    }

    /**
     * Saqlangan ma'lumotnomani ko'rish.
     */
    public function show(Resume $resume)
    {
        return view('resume.show', compact('resume'));
    }

    /**
     * Saqlangan ma'lumotnoma asosida hujjat qayta yaratish (PDF yoki DOCX).
     */
    public function regeneratePdf(Resume $resume, Request $request, ResumePdfService $service, ResumeDocxService $docxService)
    {
        return $this->downloadFromRecord(
            $resume,
            $service,
            $docxService,
            $request->query('format') === 'docx' ? 'docx' : 'pdf'
        );
    }

    /**
     * Saqlangan ma'lumotnomani o'chirish (rasmi bilan birga).
     */
    public function destroy(Resume $resume, ResumePdfService $service)
    {
        $service->deleteResumePhoto($resume);

        $resume->delete();

        return redirect()
            ->route('resume.index')
            ->with('success', "Ma'lumotnoma o'chirildi.");
    }

    /**
     * Bazadagi yozuvdan istalgan formatda hujjat qaytaradi.
     */
    protected function downloadFromRecord(Resume $resume, ResumePdfService $service, ResumeDocxService $docxService, string $format)
    {
        $base = $service->makeSafeBase($resume->full_name);

        if ($format === 'docx') {
            return $docxService->generateFromModel($resume, $base.'-malumotnoma.docx');
        }

        return $service->generateFromModel($resume, $base.'-malumotnoma.pdf');
    }
}
