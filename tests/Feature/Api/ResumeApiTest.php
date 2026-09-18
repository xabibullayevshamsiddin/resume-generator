<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        try {
            $disk = Storage::disk('local');

            foreach ($disk->allFiles('resume-photos') as $file) {
                $disk->delete($file);
            }
        } catch (\Throwable) {
            //
        }

        parent::tearDown();
    }

    /* ---------------------------------------------------------------------
     * 1) Route mavjud, throttle bilan
     * --------------------------------------------------------------------- */
    public function test_api_route_exists_with_throttle(): void
    {
        $route = Route::getRoutes()->getByName('api.resume.pdf');

        $this->assertNotNull($route);
        $this->assertStringContainsString('api/v1/resume/pdf', $route->uri());
        $this->assertContains('throttle:10,1', $route->gatherMiddleware());
    }

    /* ---------------------------------------------------------------------
     * 2) Validatsiya xatolari 422 JSON (api guruh) — xatolar maydonlardan
     * --------------------------------------------------------------------- */
    public function test_validation_errors_return_422_json(): void
    {
        $response = $this->postJson('/api/v1/resume/pdf', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'full_name', 'photo', 'birth_place', 'nationality',
            'party_affiliation', 'education', 'academic_degree',
            'academic_title', 'institution', 'employment', 'relatives',
            'home_address',
        ]);

        $message = (string) $response->json('errors.full_name.0');
        $this->assertSame('F.I.Sh. kiritilishi shart.', $message);
    }

    /* ---------------------------------------------------------------------
     * 3) Valid payload bilan 200, application/pdf, %PDF imzosi
     * --------------------------------------------------------------------- */
    public function test_api_returns_pdf_with_valid_data(): void
    {
        $response = $this->postJson('/api/v1/resume/pdf', $this->validPayload());

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('malumotnoma.pdf', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    /* ---------------------------------------------------------------------
     * 4) Noto'g'ri rasm rad etiladi (3MB limit backend'da ham)
     * --------------------------------------------------------------------- */
    public function test_api_rejects_invalid_photo(): void
    {
        $payload = $this->validPayload();
        $payload['photo'] = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->postJson('/api/v1/resume/pdf', $payload)->assertStatus(422);

        $payload['photo'] = UploadedFile::fake()->create('photo.jpg', 3500, 'image/jpeg');

        $this->postJson('/api/v1/resume/pdf', $payload)->assertStatus(422);
    }

    /* ---------------------------------------------------------------------
     * 5) Vaqtinchalik rasm tozalanadi (xatolikda ham)
     * --------------------------------------------------------------------- */
    public function test_api_temporary_photo_is_deleted(): void
    {
        $this->postJson('/api/v1/resume/pdf', $this->validPayload())->assertStatus(200);

        $this->assertCount(0, Storage::disk('local')->allFiles('resume-photos'));
    }

    /* ---------------------------------------------------------------------
     * 6) Mavjud web formadagi bilan BIR XIL multipart format: nested arrays
     * --------------------------------------------------------------------- */
    public function test_api_accepts_web_form_multipart_field_format(): void
    {
        $payload = $this->validPayload();

        // Web formasi kabi to'liq qarindosh ma'lumotlari
        $payload['relatives'] = [
            [
                'relationship' => 'Boshqa',
                'relationship_other' => 'Amakivachcha',
                'full_name' => 'Testov Uka',
                'birth_year' => '1990',
                'phone' => '+998 90 555 44 33',
            ],
        ];

        $this->postJson('/api/v1/resume/pdf', $payload)->assertStatus(200);
    }

    /* ---------------------------------------------------------------------
     * 7) XAVFSIZLIK: API'da saqlanganlar ro'yxati endpointi YO'Q
     * --------------------------------------------------------------------- */
    public function test_api_does_not_expose_saved_resumes_list(): void
    {
        \App\Models\Resume::create([
            'full_name' => 'Maxfiy Test',
            'birth_place' => 'Toshkent',
            'nationality' => "o'zbek",
            'party_affiliation' => "yo'q",
            'education' => 'oliy',
            'institution' => 'TDYU',
            'academic_degree' => "yo'q",
            'academic_title' => "yo'q",
            'employment' => [['period' => '2020', 'organization' => 'T', 'position' => '']],
            'relatives' => [['relationship' => 'Otasi', 'full_name' => 'X']],
            'home_address' => 'Maxfiy manzil',
        ]);

        // Ham /api/v1/resumes, ham /api/resumes — 404 bo'lishi SHART
        $this->getJson('/api/v1/resumes')->assertStatus(404);
        $this->getJson('/api/resumes')->assertStatus(404);
        $this->getJson('/api/v1/resumes/1')->assertStatus(404);
    }

    /* ---------------------------------------------------------------------
     * 8) Rate limit: 10 so'rov/daqiqa
     * --------------------------------------------------------------------- */
    public function test_api_rate_limiting_blocks_after_limit(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/resume/pdf', $this->validPayload())->assertStatus(200);
        }

        $this->postJson('/api/v1/resume/pdf', $this->validPayload())->assertStatus(429);
    }

    /* ---------------------------------------------------------------------
     * Helper
     * --------------------------------------------------------------------- */
    private function validPayload(): array
    {
        return [
            'full_name' => "Yarashev Sardor O'tabek o'g'li",
            'photo' => UploadedFile::fake()->image('photo.jpg', 300, 400),
            'current_status' => '2026-yildan',
            'current_organization' => 'TATU',
            'current_position' => 'Magistrant',
            'birth_date' => '2002-05-14',
            'birth_place' => 'Samarqand viloyati',
            'nationality' => "o'zbek",
            'party_affiliation' => "yo'q",
            'education' => 'oliy',
            'institution' => 'Samarqand davlat universiteti',
            'institution_year' => '2024',
            'specialty' => 'Dasturiy injiniring',
            'academic_degree' => 'bakalavr',
            'academic_title' => "yo'q",
            'languages' => 'inglizcha, ruscha',
            'employment' => [
                ['period' => '2020 — 2024', 'organization' => 'IT Park', 'position' => 'Developer'],
            ],
            'relatives' => [
                [
                    'relationship' => 'Otasi',
                    'relationship_other' => '',
                    'full_name' => 'Yarashev Otabek',
                    'birth_year' => '1975',
                    'birth_place' => 'Kattaqo\'rg\'on',
                    'workplace' => 'Maktab',
                    'position' => "O'qituvchi",
                    'address' => 'Samarqand',
                    'phone' => '+998 90 111 22 33',
                ],
            ],
            'phone' => '+998 90 123 45 67',
            'home_address' => "Samarqand sh., Registon ko'chasi 1",
            'passport_info' => 'AA1234567',
        ];
    }
}
