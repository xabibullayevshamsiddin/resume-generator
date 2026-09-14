<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumePdfTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Test davomida saqlangan vaqtinchalik rasmlarni tozalash
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
     * 1) Forma sahifasi ochiladi va kerakli maydonlar mavjud
     * --------------------------------------------------------------------- */
    public function test_form_page_opens_with_required_fields(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee("Ma'lumotnoma generatori", false);
        $response->assertSee('name="full_name"', false);
        $response->assertSee('name="photo"', false);
        $response->assertSee('name="birth_place"', false);
        $response->assertSee('name="nationality"', false);
        $response->assertSee('name="party_affiliation"', false);
        $response->assertSee('name="education"', false);
        $response->assertSee('name="academic_degree"', false);
        $response->assertSee('name="academic_title"', false);
        $response->assertSee('name="institution"', false);
        $response->assertSee('name="home_address"', false);
        $response->assertSee('name="employment[0][period]"', false);
        $response->assertSee('name="relatives[0][relationship]"', false);
    }

    /* ---------------------------------------------------------------------
     * 2) CSRF himoyasi ishlaydi
     * --------------------------------------------------------------------- */
    public function test_csrf_protection_is_enforced(): void
    {
        // Laravel testing muhitida skeleton token tekshiruvini o'tkazib yuboradi,
        // shuning uchun: formada token borligi va route web (CSRF) guruhida ekanligi tekshiriladi.
        $response = $this->get(route('resume.form'));

        $response->assertSee('name="csrf-token"', false);
        $response->assertSee('name="_token"', false);

        $route = Route::getRoutes()->getByName('resume.pdf');

        $this->assertNotNull($route);
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('throttle:10,1', $route->gatherMiddleware());
    }

    /* ---------------------------------------------------------------------
     * 3) Majburiy maydonlar bo'sh bo'lsa validation xatosi qaytadi
     * --------------------------------------------------------------------- */
    public function test_validation_fails_when_required_fields_missing(): void
    {
        $response = $this->post(route('resume.pdf'), []);

        $response->assertSessionHasErrors([
            'full_name', 'photo', 'birth_place', 'nationality',
            'party_affiliation', 'education', 'academic_degree',
            'academic_title', 'institution', 'employment', 'relatives',
            'home_address',
        ]);
    }

    public function test_validation_error_messages_are_in_uzbek(): void
    {
        $response = $this->post(route('resume.pdf'), []);

        $response->assertSessionHasErrors();
        $errors = session('errors')->all();

        $this->assertStringContainsString("F.I.Sh. kiritilishi shart.", implode(' ', $errors));
        $this->assertStringContainsString('Profil rasmi yuklanishi shart.', implode(' ', $errors));
    }

    /* ---------------------------------------------------------------------
     * 4) Noto'g'ri rasm turi va 3MB dan katta rasm rad etiladi
     * --------------------------------------------------------------------- */
    public function test_invalid_photo_type_is_rejected(): void
    {
        $payload = $this->validPayload();
        $payload['photo'] = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->post(route('resume.pdf'), $payload);

        $response->assertSessionHasErrors('photo');
    }

    public function test_oversized_photo_is_rejected(): void
    {
        $payload = $this->validPayload();
        $payload['photo'] = UploadedFile::fake()->create('photo.jpg', 3500, 'image/jpeg');

        $response = $this->post(route('resume.pdf'), $payload);

        $response->assertSessionHasErrors('photo');
    }

    /* ---------------------------------------------------------------------
     * 5) Employment/relatives array validatsiyasi (min/max)
     * --------------------------------------------------------------------- */
    public function test_employment_array_limits_are_enforced(): void
    {
        $payload = $this->validPayload();

        $payload['employment'] = [];
        $this->post(route('resume.pdf'), $payload)->assertSessionHasErrors('employment');

        $payload['employment'] = [];
        for ($i = 0; $i < 21; $i++) {
            $payload['employment'][] = [
                'period' => '2020',
                'organization' => 'Org',
                'position' => 'Dev',
            ];
        }
        $this->post(route('resume.pdf'), $payload)->assertSessionHasErrors('employment');
    }

    public function test_relatives_array_limits_are_enforced(): void
    {
        $payload = $this->validPayload();
        $payload['relatives'] = [];

        $this->post(route('resume.pdf'), $payload)->assertSessionHasErrors('relatives');

        $payload['relatives'] = [];
        for ($i = 0; $i < 11; $i++) {
            $payload['relatives'][] = [
                'relationship' => 'Otasi',
                'full_name' => 'Test Testov',
            ];
        }
        $this->post(route('resume.pdf'), $payload)->assertSessionHasErrors('relatives');
    }

    public function test_relative_other_is_required_when_relationship_is_other(): void
    {
        $payload = $this->validPayload();
        $payload['relatives'][0]['relationship'] = 'Boshqa';
        $payload['relatives'][0]['relationship_other'] = '';

        $this->post(route('resume.pdf'), $payload)->assertSessionHasErrors('relatives.0.relationship_other');
    }

    public function test_party_affiliation_other_is_required_when_boshqa(): void
    {
        $payload = $this->validPayload();
        $payload['party_affiliation'] = 'boshqa';
        $payload['party_affiliation_other'] = '';

        $this->post(route('resume.pdf'), $payload)->assertSessionHasErrors('party_affiliation_other');
    }

    /* ---------------------------------------------------------------------
     * 6) PDF endpoint valid ma'lumot bilan application/pdf qaytaradi
     * --------------------------------------------------------------------- */
    public function test_pdf_endpoint_returns_pdf_with_valid_data(): void
    {
        $response = $this->post(route('resume.pdf'), $this->validPayload());

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', (string) $response->headers->get('Content-Disposition'));

        $body = $response->getContent();
        $this->assertStringStartsWith('%PDF', $body);
    }

    public function test_pdf_filename_is_a_safe_slug(): void
    {
        $response = $this->post(route('resume.pdf'), $this->validPayload());

        $disposition = (string) $response->headers->get('Content-Disposition');

        $this->assertMatchesRegularExpression(
            '/filename="?[a-z0-9\-]+-malumotnoma\.pdf"?/',
            $disposition
        );
    }

    /* ---------------------------------------------------------------------
     * 8) Vaqtinchalik rasm PDF'dan keyin (xatolikda ham) o'chiriladi
     * --------------------------------------------------------------------- */
    public function test_temporary_photo_is_deleted_after_pdf_generation(): void
    {
        $this->post(route('resume.pdf'), $this->validPayload())->assertStatus(200);

        $disk = Storage::disk('local');
        $this->assertCount(0, $disk->allFiles('resume-photos'));
    }

    public function test_temporary_photo_is_deleted_when_validation_fails(): void
    {
        // Rasm validatsiyadan o'tsa-da, boshqa maydon xato bersa — hech qanday
        // fayl saqlanmaydi (validatsiya PDF yaratishdan OLDIN bajariladi).
        $payload = $this->validPayload();
        $payload['home_address'] = ''; // required maydon bo'shatildi

        $response = $this->post(route('resume.pdf'), $payload);

        $response->assertSessionHasErrors('home_address');
        $this->assertCount(0, Storage::disk('local')->allFiles('resume-photos'));
    }

    /* ---------------------------------------------------------------------
     * 9) Pasport/telefon logga yozilmaydi
     * --------------------------------------------------------------------- */
    public function test_sensitive_data_is_not_logged(): void
    {
        config(['logging.default' => 'single', 'logging.channels.single.path' => storage_path('logs/laravel-test.log')]);

        $payload = $this->validPayload();
        $payload['phone'] = '+998 90 999 88 77';
        $payload['passport_info'] = 'AA9999999';
        $payload['home_address'] = 'Maxfiy manzil 99';

        $this->post(route('resume.pdf'), $payload)->assertStatus(200);

        $logContents = '';

        if (file_exists(storage_path('logs/laravel-test.log'))) {
            $logContents = (string) file_get_contents(storage_path('logs/laravel-test.log'));
        }

        $this->assertStringNotContainsString('+998 90 999 88 77', $logContents);
        $this->assertStringNotContainsString('AA9999999', $logContents);
        $this->assertStringNotContainsString('Maxfiy manzil 99', $logContents);
    }

    /* ---------------------------------------------------------------------
     * 10) Rate limit ishlaydi
     * --------------------------------------------------------------------- */
    public function test_rate_limiting_blocks_after_limit(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('resume.pdf'), $this->validPayload())->assertStatus(200);
        }

        $this->post(route('resume.pdf'), $this->validPayload())->assertStatus(429);
    }

    /* ---------------------------------------------------------------------
     * Database: saqlash + ro'yxat + qayta yaratish + o'chirish
     * --------------------------------------------------------------------- */
    public function test_save_record_stores_resume_in_database(): void
    {
        $payload = $this->validPayload();
        $payload['save_record'] = '1';

        $response = $this->post(route('resume.pdf'), $payload);

        $response->assertStatus(200);
        $this->assertDatabaseCount('resumes', 1);

        $resume = \App\Models\Resume::first();
        $this->assertSame("Yarashev Sardor O'tabek o'g'li", $resume->full_name);
        $this->assertSame('Samarqand viloyati', $resume->birth_place);
        $this->assertCount(1, $resume->employment);
        $this->assertCount(1, $resume->relatives);
    }

    public function test_sensitive_fields_are_encrypted_in_database(): void
    {
        $payload = $this->validPayload();
        $payload['save_record'] = '1';

        $this->post(route('resume.pdf'), $payload);

        $raw = \Illuminate\Support\Facades\DB::table('resumes')->first();

        // Bazada xom holda YO'Q, faqat shifrlangan bo'ladi
        $this->assertStringNotContainsString('+998 90 123 45 67', $raw->phone);
        $this->assertStringNotContainsString("Registon ko'chasi", $raw->home_address);
        $this->assertStringNotContainsString('AA1234567', $raw->passport_info);

        // Model orqali o'qilganda to'g'ri qaytadi
        $resume = \App\Models\Resume::first();
        $this->assertSame('+998 90 123 45 67', $resume->phone);
    }

    public function test_index_page_lists_saved_resumes(): void
    {
        \App\Models\Resume::create([
            'full_name' => 'Testov Test Testovich',
            'birth_place' => 'Toshkent',
            'nationality' => "o'zbek",
            'party_affiliation' => "yo'q",
            'education' => 'oliy',
            'institution' => 'TDYU',
            'academic_degree' => "yo'q",
            'academic_title' => "yo'q",
            'employment' => [['period' => '2020', 'organization' => 'Test', 'position' => '']],
            'relatives' => [['relationship' => 'Otasi', 'full_name' => 'X']],
            'home_address' => 'Toshkent sh.',
        ]);

        $response = $this->get(route('resume.index'));

        $response->assertStatus(200);
        $response->assertSee('Testov Test Testovich');
    }

    public function test_regenerate_pdf_from_saved_record(): void
    {
        $resume = \App\Models\Resume::create([
            'full_name' => "Yarashev Sardor O'tabek o'g'li",
            'birth_place' => 'Samarqand',
            'nationality' => "o'zbek",
            'party_affiliation' => "yo'q",
            'education' => 'oliy',
            'institution' => 'SamDU',
            'academic_degree' => 'bakalavr',
            'academic_title' => "yo'q",
            'employment' => [['period' => '2020', 'organization' => 'IT Park', 'position' => 'Dev']],
            'relatives' => [['relationship' => 'Otasi', 'full_name' => 'X']],
            'home_address' => 'Samarqand sh.',
        ]);

        $response = $this->get(route('resume.regenerate', $resume));

        $response->assertStatus(200);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_destroy_removes_resume_and_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $resume = new \App\Models\Resume([
            'full_name' => 'Ochiriladigan Test',
            'birth_place' => 'Toshkent',
            'nationality' => "o'zbek",
            'party_affiliation' => "yo'q",
            'education' => 'oliy',
            'institution' => 'TDYU',
            'academic_degree' => "yo'q",
            'academic_title' => "yo'q",
            'employment' => [['period' => '2020', 'organization' => 'T', 'position' => '']],
            'relatives' => [['relationship' => 'Otasi', 'full_name' => 'X']],
            'home_address' => 'Toshkent',
            'photo_path' => 'resume-photos/photo-test.jpg',
        ]);
        $resume->save();

        \Illuminate\Support\Facades\Storage::disk('local')->put('resume-photos/photo-test.jpg', 'fake');

        $response = $this->delete(route('resume.destroy', $resume));

        $response->assertRedirect(route('resume.index'));
        $this->assertDatabaseMissing('resumes', ['id' => $resume->id]);
        \Illuminate\Support\Facades\Storage::disk('local')->assertMissing('resume-photos/photo-test.jpg');
    }

    /* ---------------------------------------------------------------------
     * Helper: to'liq valid payload
     * --------------------------------------------------------------------- */
    private function validPayload(): array
    {
        return [
            'full_name' => "Yarashev Sardor O'tabek o'g'li",
            'photo' => UploadedFile::fake()->image('photo.jpg', 300, 400),
            'current_status' => '2026-yil 7-sentyabrdan',
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
            'awards' => '',
            'elected_bodies' => '',
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
            'home_address' => 'Samarqand sh., Registon ko\'chasi 1',
            'passport_info' => 'AA1234567',
        ];
    }
}
