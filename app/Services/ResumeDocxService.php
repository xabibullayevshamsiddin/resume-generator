<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Ma'lumotnomaning Word (DOCX) versiyasini yaratadi.
 * Struktura PDF versiyasiga mos: 1-sahifa — shaxsiy ma'lumotlar + mehnat
 * faoliyati, 2-sahifa — yaqin qarindoshlar + aloqa ma'lumotlari.
 * Formatlash helperlari ResumePdfService'dan qayta ishlatiladi (bitta manba).
 */
class ResumeDocxService
{
    /**
     * A4: 210×297mm → twips (1mm ≈ 56.7). Maydonlar: 12mm tepa/past, 15mm yon.
     */
    protected const PAGE = [
        'pageSizeW' => 11906,
        'pageSizeH' => 16838,
        'marginTop' => 680,
        'marginBottom' => 680,
        'marginLeft' => 850,
        'marginRight' => 850,
    ];

    protected const FONT = 'Times New Roman';

    public function __construct(protected ResumePdfService $pdfService)
    {
    }

    /**
     * Tayyor view-data (buildViewData natijasi) asosida DOCX download.
     */
    public function generateFromViewData(array $data, ?string $photoPath, string $filename)
    {
        $phpWord = $this->buildDocument($data, $photoPath);

        return response()->streamDownload(
            function () use ($phpWord) {
                IOFactory::createWriter($phpWord, 'Word2007')->save('php://output');
            },
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }

    /**
     * Bazadagi saqlangan ma'lumotnoma asosida DOCX qayta yaratadi.
     */
    public function generateFromModel($resume, string $filename)
    {
        $data = $this->pdfService->buildViewData($resume->toFormData(), $resume->photo_path);

        return $this->generateFromViewData($data, $resume->photo_path, $filename);
    }

    /**
     * Hujjatni yig'adi (PDF sahifa tuzilishining o'zi).
     */
    protected function buildDocument(array $d, ?string $photoPath): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection(self::PAGE);

        // Profil rasmi — o'ng yuqorida (35×45mm)
        if ($photoPath !== null && Storage::disk('local')->exists($photoPath)) {
            $section->addImage(Storage::disk('local')->path($photoPath), [
                'width' => 132,
                'height' => 170,
                'alignment' => Jc::RIGHT,
            ]);
        }

        // F.I.Sh. va joriy holat — markazda
        $section->addText($d['fullName'], ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);

        $statusLine = trim($d['currentStatus'].($d['currentOrganization'] !== 'yo\'q' ? ', '.$d['currentOrganization'] : ''));
        if ($statusLine !== 'yo\'q') {
            $section->addText($statusLine, ['size' => 12], ['alignment' => Jc::CENTER, 'spaceAfter' => 160]);
        } else {
            $section->addText('', ['size' => 6], ['spaceAfter' => 120]);
        }

        // Shaxsiy ma'lumotlar — 4 ustunli jadval
        $personal = $section->addTable(['borderSize' => 4, 'borderColor' => '7f7f7f', 'cellMargin' => 60]);
        $this->personalRow($personal, [
            "Tug'ilgan sanasi", $d['birthDate'], 'Millati', $d['nationality'],
        ]);
        $this->personalRow($personal, [
            "Tug'ilgan joyi", $d['birthPlace'], "Partiyaviyligi", $d['partyAffiliation'],
        ]);
        $this->personalRow($personal, [
            "Ma'lumoti", $d['education'], 'Tamomlagan', $d['institution'],
        ]);
        $this->personalRow($personal, [
            'Tamomlagan yili', $d['institutionYear'], 'Mutaxassisligi', $d['specialty'],
        ]);
        $this->personalRow($personal, [
            'Ilmiy darajasi', $d['academicDegree'], 'Ilmiy unvoni', $d['academicTitle'],
        ]);
        $this->personalRow($personal, [
            'Chet tillari', $d['languages'], 'Harbiy unvoni', $d['militaryRank'],
        ]);
        $this->personalRow($personal, [
            'Davlat mukofotlari', $d['awards'], 'Deputatligi', $d['electedBodies'],
        ]);

        // MEHNAT FAOLIYATI
        $section->addText('MEHNAT FAOLIYATI', ['bold' => true, 'size' => 13], ['alignment' => Jc::CENTER, 'spaceBefore' => 200, 'spaceAfter' => 100]);

        $employment = $section->addTable(['borderSize' => 4, 'borderColor' => '7f7f7f', 'cellMargin' => 60]);
        $this->headerRow($employment, [2800, 7400], ['Davri', 'Tashkilot, lavozimi']);

        foreach ($d['employmentRows'] as $row) {
            $tr = $employment->addRow();
            $tr->addCell(2800)->addText($row['period']);
            $cell = $tr->addCell(7400);
            $cell->addText($row['organization']);
        }

        // 2-sahifa — qarindoshlar
        $section->addPageBreak();

        $section->addText(
            $d['fullName']."ning yaqin qarindoshlari haqida MA'LUMOT",
            ['bold' => true, 'size' => 13],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 140]
        );

        $relatives = $section->addTable(['borderSize' => 4, 'borderColor' => '7f7f7f', 'cellMargin' => 60]);
        $this->headerRow(
            $relatives,
            [1300, 2700, 2100, 2100, 2000],
            ['Qarindoshligi', 'F.I.Sh.', "Tug'ilgan yili, joyi", 'Ish joyi, lavozimi', 'Turar joyi, telefoni']
        );

        foreach ($d['relativeRows'] as $row) {
            $tr = $relatives->addRow();
            $tr->addCell(1300)->addText($row['relationship']);
            $tr->addCell(2700)->addText($row['fullName']);

            $birth = $tr->addCell(2100);
            $birth->addText($row['birth']['main']);
            $birth->addText($row['birth']['sub'], ['size' => 10]);

            $work = $tr->addCell(2100);
            $work->addText($row['work']['main']);
            $work->addText($row['work']['sub'], ['size' => 10]);

            $address = $tr->addCell(2000);
            $address->addText($row['address']['main']);
            $address->addText($row['address']['sub'], ['size' => 10]);
        }

        // Yakuniy ma'lumotlar
        $section->addText(' ', ['size' => 8], ['spaceAfter' => 80]);
        $this->labeledLine($section, 'Mobil raqami', $d['phone']);
        $this->labeledLine($section, 'Uy manzili', $d['homeAddress']);
        $this->labeledLine($section, "Pasport ma'lumotlari", $d['passportInfo']);

        return $phpWord;
    }

    /**
     * 4 ustunli shaxsiy ma'lumot qatori: label | value | label | value.
     */
    protected function personalRow($table, array $cells): void
    {
        $tr = $table->addRow();

        $tr->addCell(2200)->addText($cells[0], ['bold' => true]);
        $tr->addCell(2900)->addText($cells[1]);
        $tr->addCell(2200)->addText($cells[2], ['bold' => true]);
        $tr->addCell(2900)->addText($cells[3]);
    }

    /**
     * Jadval sarlavha qatori — har sahifada takrorlanadi (tblHeader).
     */
    protected function headerRow($table, array $widths, array $labels): void
    {
        $tr = $table->addRow(null, ['tblHeader' => true]);

        foreach ($labels as $i => $label) {
            $tr->addCell($widths[$i])->addText($label, ['bold' => true]);
        }
    }

    /**
     * Qalin label + qiymat bilan yangi qator.
     */
    protected function labeledLine($section, string $label, string $value): void
    {
        $section->addText($label.':', ['bold' => true], ['spaceBefore' => 80]);
        $section->addText($value, [], ['spaceAfter' => 40]);
    }
}
