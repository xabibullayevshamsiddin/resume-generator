<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateResumeRequest;
use App\Models\Resume;
use App\Services\ResumePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $photo = $request->file('photo');

        // Agar foydalanuvchi "saqlash"ni tanlagan bo'lsa — bazaga yozamiz
        if ($request->boolean('save_record')) {
            $resume = $service->storeResume($validated, $photo);

            // PDF'ni saqlangan rasm bilan yaratamiz
            $pdfData = $service->buildViewData($resume->toFormData(), $resume->photo_path);

            return $service->renderPdf($pdfData, $filename);
        }

        return $service->generateDownload($request, $filename);
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
     * Saqlangan ma'lumotnoma asosida PDF qayta yaratish.
     */
    public function regeneratePdf(Resume $resume, ResumePdfService $service)
    {
        $filename = $service->makeSafeFilename($resume->full_name);

        return $service->generateFromModel($resume, $filename);
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
}
