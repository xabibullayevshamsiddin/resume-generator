<?php

namespace App\Services;

use App\Http\Requests\GenerateResumeRequest;
use App\Models\Resume;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ResumePdfService
{
    /**
     * Majburiy select maydonlari bo'sh qolganda ishlatiladigan standart matn.
     */
    public const DEFAULT_VALUE = 'yo\'q';

    /**
     * Jadvallardagi bo'sh katak uchun standart belgi.
     */
    public const DASH = '—';

    /**
     * Vaqtinchalik rasm saqlanadigan disk — FAQAT `local` (storage/app),
     * hech qachon `public` disk emas (u tashqi web'dan ochiq bo'ladi).
     */
    protected const PHOTO_DISK = 'local';

    protected const PHOTO_DIRECTORY = 'resume-photos';

    /**
     * Hujjat HTML'ini yig'adi, metadata qo'yadi va download response qaytaradi.
     *
 * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Barryvdh\DomPDF\PDF
     */
    public function generateDownload(GenerateResumeRequest $request, string $filename)
    {
        $photoPath = $this->storeTemporaryPhoto($request->file('photo'));

        try {
            $data = $this->buildViewData($request->validated(), $photoPath);

            return $this->renderPdf($data, $filename);
        } finally {
            // Xatolik yuz berganda ham vaqtinchalik rasm o'chiriladi.
            $this->deleteTemporaryPhoto($photoPath);
        }
    }

    /**
     * API (mobil ilova) uchun: vaqtinchalik rasm bilan PDF obyektini qaytaradi.
     * Rasm `finally`da o'chiriladi (xatolikda ham) — maxfiylik o'zgarmadi.
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdfBinary(array $validated, ?UploadedFile $photo)
    {
        $photoPath = $photo !== null ? $this->storeTemporaryPhoto($photo) : null;

        try {
            $data = $this->buildViewData($validated, $photoPath);

            $pdf = Pdf::loadHtml(view('resume.pdf', $data)->render());

            $pdf->getDomPDF()->add_info('Title', $data['fullName']." — Ma'lumotnoma");

            return $pdf;
        } finally {
            $this->deleteTemporaryPhoto($photoPath);
        }
    }

    /**
     * Bazadagi saqlangan ma'lumotnoma asosida PDF qayta yaratadi.
     */
    public function generateFromModel(Resume $resume, string $filename)
    {
        return $this->renderPdf(
            $this->buildViewData($resume->toFormData(), $resume->photo_path),
            $filename
        );
    }

    /**
     * Umumiy PDF render — HTML yig'adi, metadata qo'yadi, download qaytaradi.
     */
    public function renderPdf(array $data, string $filename)
    {
        $pdf = Pdf::loadHtml(
            view('resume.pdf', $data)->render()
        );

        $dompdf = $pdf->getDomPDF();
        $dompdf->add_info('Title', $data['fullName']." — Ma'lumotnoma");
        $dompdf->add_info('Author', "Ma'lumotnoma generatori");

        return $pdf->download($filename);
    }

    /**
     * Bazaga yangi ma'lumotnoma saqlaydi (rasm bilan).
     * Rasm doimiy ravishda faqat `local` (private) diskda turadi.
     */
    public function storeResume(array $validated, ?UploadedFile $photo): Resume
    {
        $photoPath = null;

        if ($photo !== null) {
            $mime = (string) $photo->getMimeType();
            $extension = match ($mime) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            $path = $photo->storeAs(
                self::PHOTO_DIRECTORY,
                'resume-'.Str::uuid()->toString().'.'.$extension,
                self::PHOTO_DISK
            );

            $photoPath = is_string($path) ? $path : null;
        }

        return Resume::create([
            'full_name' => $validated['full_name'],
            'current_status' => $validated['current_status'] ?? null,
            'current_organization' => $validated['current_organization'] ?? null,
            'current_position' => $validated['current_position'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'birth_place' => $validated['birth_place'],
            'nationality' => $validated['nationality'],
            'party_affiliation' => $validated['party_affiliation'],
            'party_affiliation_other' => $validated['party_affiliation_other'] ?? null,
            'military_rank' => $validated['military_rank'] ?? null,
            'education' => $validated['education'],
            'institution' => $validated['institution'],
            'institution_year' => $validated['institution_year'] ?? null,
            'specialty' => $validated['specialty'] ?? null,
            'academic_degree' => $validated['academic_degree'],
            'academic_degree_other' => $validated['academic_degree_other'] ?? null,
            'academic_title' => $validated['academic_title'],
            'academic_title_other' => $validated['academic_title_other'] ?? null,
            'languages' => $validated['languages'] ?? null,
            'awards' => $validated['awards'] ?? null,
            'elected_bodies' => $validated['elected_bodies'] ?? null,
            'employment' => $validated['employment'],
            'relatives' => $validated['relatives'],
            'phone' => $validated['phone'] ?? null,
            'home_address' => $validated['home_address'],
            'passport_info' => $validated['passport_info'] ?? null,
            'photo_path' => $photoPath,
        ]);
    }

    /**
     * Saqlangan ma'lumotnoma rasmini o'chiradi.
     */
    public function deleteResumePhoto(Resume $resume): void
    {
        if (filled($resume->photo_path)) {
            Storage::disk(self::PHOTO_DISK)->delete($resume->photo_path);
        }
    }

    /**
     * Blade view'ga uzatiladigan formatlangan ma'lumotlar.
     */
    public function buildViewData(array $validated, ?string $photoPath): array
    {
        return [
            'fullName' => $validated['full_name'],
            'photoDataUri' => $this->photoDataUri($photoPath),

            'currentStatus' => $this->valueOrDefault($validated['current_status'] ?? null),
            'currentOrganization' => $this->valueOrDefault($validated['current_organization'] ?? null),
            'currentPosition' => $this->valueOrDefault($validated['current_position'] ?? null),

            'birthDate' => $this->formatDate($validated['birth_date'] ?? null),
            'birthPlace' => $validated['birth_place'],
            'nationality' => $validated['nationality'],
            'partyAffiliation' => $this->withOther(
                $validated['party_affiliation'],
                $validated['party_affiliation_other'] ?? null
            ),
            'militaryRank' => $this->valueOrDefault($validated['military_rank'] ?? null),

            'education' => $validated['education'],
            'institution' => $validated['institution'],
            'institutionYear' => $this->valueOrDefault($validated['institution_year'] ?? null),
            'specialty' => $this->valueOrDefault($validated['specialty'] ?? null),
            'academicDegree' => $this->withOther(
                $validated['academic_degree'],
                $validated['academic_degree_other'] ?? null
            ),
            'academicTitle' => $this->withOther(
                $validated['academic_title'],
                $validated['academic_title_other'] ?? null
            ),

            'languages' => $this->valueOrDefault($validated['languages'] ?? null),
            'awards' => $this->valueOrDefault($validated['awards'] ?? null),
            'electedBodies' => $this->valueOrDefault($validated['elected_bodies'] ?? null),

            'employmentRows' => $this->prepareEmploymentRows($validated['employment']),
            'relativeRows' => $this->prepareRelativeRows($validated['relatives']),

            'phone' => $this->valueOrDefault($validated['phone'] ?? null),
            'homeAddress' => $validated['home_address'],
            'passportInfo' => $this->valueOrDefault($validated['passport_info'] ?? null),
        ];
    }

    /**
     * Mehnat faoliyati qatorlarini hujjat uchun tayyorlaydi.
     */
    public function prepareEmploymentRows(array $employment): array
    {
        $rows = [];

        foreach ($employment as $item) {
            $rows[] = [
                'period' => $this->valueOrDefault($item['period'] ?? null),
                'organization' => $this->valueOrDefault($item['organization'] ?? null),
                'position' => $item['position'] ?? '',
            ];
        }

        return $rows;
    }

    /**
     * Qarindoshlar qatorlarini hujjat uchun tayyorlaydi.
     * Tug'ilgan yili+joyi, ish joyi+lavozimi, manzil+telefon — bitta katakda
     * ikki qator (asosiy qator + pastki satr) ko'rinishida.
     */
    public function prepareRelativeRows(array $relatives): array
    {
        $rows = [];

        foreach ($relatives as $relative) {
            $relationship = $relative['relationship'] ?? '';

            $rows[] = [
                'relationship' => $relationship === 'Boshqa'
                    ? trim($relationship.': '.($relative['relationship_other'] ?? ''))
                    : $relationship,
                'fullName' => $relative['full_name'] ?? '',
                'birth' => [
                    'main' => $this->valueOrDefault($relative['birth_year'] ?? null),
                    'sub' => $this->valueOrDefault($relative['birth_place'] ?? null),
                ],
                'work' => [
                    'main' => $this->valueOrDefault($relative['workplace'] ?? null),
                    'sub' => $this->valueOrDefault($relative['position'] ?? null),
                ],
                'address' => [
                    'main' => $this->valueOrDefault($relative['address'] ?? null),
                    'sub' => $this->valueOrDefault($relative['phone'] ?? null),
                ],
            ];
        }

        return $rows;
    }

    /**
     * Sana bilan kelgan qiymatni `22.07.2008` formatiga keltiradi.
     */
    public function formatDate(mixed $value): string
    {
        if (! filled($value)) {
            return self::DASH;
        }

        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /**
     * Bo'sh qiymatni hujjatdagi standart ko'rinishga keltiradi.
     */
    public function valueOrDefault(mixed $value): string
    {
        if (! filled($value)) {
            return self::DEFAULT_VALUE;
        }

        return trim((string) $value);
    }

    /**
     * Select qiymati `boshqa` bo'lsa izoh bilan birga qaytaradi.
     */
    protected function withOther(string $value, ?string $other): string
    {
        if (Str::lower($value) === 'boshqa' && filled($other)) {
            return 'Boshqa: '.trim((string) $other);
        }

        return $this->valueOrDefault($value);
    }

    /**
     * F.I.Sh.dan xavfsiz PDF filename yaratadi:
     * faqat lotin harf/raqam/tire qoladi, o'zbek harflari translit qilinadi.
     * Masalan: "Yarashev Sardor O'tabek o'g'li" -> yarashev-sardor-otabek-ogli-malumotnoma.pdf
     */
    public function makeSafeFilename(string $fullName): string
    {
        $base = $this->makeSafeBase($fullName);

        // Fallback slug allaqachon 'malumotnoma' — qo'shimcha qo'shilmaydi
        return $base === 'malumotnoma'
            ? 'malumotnoma.pdf'
            : $base.'-malumotnoma.pdf';
    }

    /**
     * F.I.Sh.dan xavfsiz slug (fayl nomisiz) — PDF va DOCX formatlari
     * o'z kengaytmasi bilan qo'shadi. Slug bo'sh chiqsa 'malumotnoma' qaytaradi.
     */
    public function makeSafeBase(string $fullName): string
    {
        $slug = Str::of($fullName)
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->replaceMatches('/-+/', '-')
            ->trim('-')
            ->limit(60, '');

        $slug = trim((string) $slug, '-');

        return filled($slug) ? $slug : 'malumotnoma';
    }

    /**
     * Rasmni server tomonidan xavfsiz nom bilan `local` diskka saqlaydi.
     * MIME turi bu yerda qayta tekshiriladi (client-side tekshiruvga ishonilmaydi).
     */
    public function storeTemporaryPhoto(UploadedFile $photo): string
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        $mime = (string) $photo->getMimeType();

        if (! in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('Noto\'g\'ri rasm formati: '.$mime);
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        };

        $path = (string) $photo->storeAs(
            self::PHOTO_DIRECTORY,
            'photo-'.Str::uuid()->toString().'.'.$extension,
            self::PHOTO_DISK
        );

        if (! Storage::disk(self::PHOTO_DISK)->exists($path)) {
            throw new RuntimeException('Rasm saqlanmadi.');
        }

        return $path;
    }

    /**
     * Vaqtinchalik rasmni o'chiradi (mavjud bo'lsa).
     */
    public function deleteTemporaryPhoto(?string $path): void
    {
        if (filled($path)) {
            Storage::disk(self::PHOTO_DISK)->delete($path);
        }
    }

    /**
     * DomPDF uchun rasmni data URI sifatida tayyorlaydi.
     * `enable_remote` o'chirilgani uchun hujjat faqat inline ma'lumotlardan quriladi.
     */
    protected function photoDataUri(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        $disk = Storage::disk(self::PHOTO_DISK);

        if (! $disk->exists($path)) {
            return null;
        }

        $mime = $disk->mimeType($path) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode((string) $disk->get($path));
    }
}
