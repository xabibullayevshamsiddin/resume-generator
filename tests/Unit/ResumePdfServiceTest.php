<?php

namespace Tests\Unit;

use App\Services\ResumePdfService;
use PHPUnit\Framework\TestCase;

class ResumePdfServiceTest extends TestCase
{
    public function test_filename_is_safe_for_latin_names(): void
    {
        $service = new ResumePdfService();

        $this->assertSame(
            'yarashev-sardor-otabek-ogli-malumotnoma.pdf',
            $service->makeSafeFilename('Yarashev Sardor Otabek ogli')
        );
    }

    public function test_filename_handles_uzbek_apostrophes(): void
    {
        $service = new ResumePdfService();

        $filename = $service->makeSafeFilename("Yarashev Sardor O'tabek o'g'li");

        $this->assertStringEndsWith('-malumotnoma.pdf', $filename);
        $this->assertDoesNotMatchRegularExpression('/[^a-z0-9\-.]/', $filename);
        $this->assertStringNotContainsString('..', $filename);
        $this->assertStringNotContainsString('/', $filename);
        $this->assertStringNotContainsString('\\', $filename);
    }

    public function test_filename_falls_back_when_only_special_chars(): void
    {
        $service = new ResumePdfService();

        $this->assertSame('malumotnoma.pdf', $service->makeSafeFilename('...///\\\\'));
    }

    public function test_filename_is_limited_in_length(): void
    {
        $service = new ResumePdfService();

        $filename = $service->makeSafeFilename(str_repeat('Averylongname ', 30));

        $this->assertLessThanOrEqual(80, strlen($filename));
        $this->assertStringEndsWith('-malumotnoma.pdf', $filename);
    }

    public function test_format_date_outputs_dot_format(): void
    {
        $service = new ResumePdfService();

        $this->assertSame('22.07.2008', $service->formatDate('2008-07-22'));
        $this->assertSame('—', $service->formatDate(null));
        $this->assertSame('—', $service->formatDate(''));
    }

    public function test_format_date_keeps_arbitrary_text(): void
    {
        $service = new ResumePdfService();

        $this->assertSame('2008-yil 22-iyul', $service->formatDate('2008-yil 22-iyul'));
    }

    public function test_value_or_default_replaces_empty_values(): void
    {
        $service = new ResumePdfService();

        $this->assertSame("yo'q", $service->valueOrDefault(null));
        $this->assertSame("yo'q", $service->valueOrDefault('   '));
        $this->assertSame('oliy', $service->valueOrDefault(' oliy '));
    }

    public function test_employment_rows_are_prepared(): void
    {
        $service = new ResumePdfService();

        $rows = $service->prepareEmploymentRows([
            ['period' => '2020-2024', 'organization' => 'IT Park', 'position' => 'Developer'],
            ['period' => '', 'organization' => '   ', 'position' => null],
        ]);

        $this->assertCount(2, $rows);
        $this->assertSame('2020-2024', $rows[0]['period']);
        $this->assertSame('IT Park', $rows[0]['organization']);
        $this->assertSame('Developer', $rows[0]['position']);
        $this->assertSame("yo'q", $rows[1]['period']);
        $this->assertSame("yo'q", $rows[1]['organization']);
        $this->assertSame('', $rows[1]['position']);
    }

    public function test_relative_rows_are_prepared_with_other(): void
    {
        $service = new ResumePdfService();

        $rows = $service->prepareRelativeRows([
            [
                'relationship' => 'Boshqa',
                'relationship_other' => 'Amakivachcha',
                'full_name' => 'Testov Test',
                'birth_year' => '1990',
                'birth_place' => 'Toshkent',
                'workplace' => 'Maktab',
                'position' => "O'qituvchi",
                'address' => 'Toshkent sh.',
                'phone' => '+998 90 123 45 67',
            ],
        ]);

        $this->assertSame('Boshqa: Amakivachcha', $rows[0]['relationship']);
        $this->assertSame('1990', $rows[0]['birth']['main']);
        $this->assertSame('Toshkent', $rows[0]['birth']['sub']);
        $this->assertSame('Maktab', $rows[0]['work']['main']);
        $this->assertSame("O'qituvchi", $rows[0]['work']['sub']);
        $this->assertSame('Toshkent sh.', $rows[0]['address']['main']);
        $this->assertSame('+998 90 123 45 67', $rows[0]['address']['sub']);
    }

    public function test_relative_rows_without_other_use_relationship_directly(): void
    {
        $service = new ResumePdfService();

        $rows = $service->prepareRelativeRows([
            ['relationship' => 'Otasi', 'full_name' => 'Test'],
        ]);

        $this->assertSame('Otasi', $rows[0]['relationship']);
        $this->assertSame("yo'q", $rows[0]['birth']['main']);
    }
}
