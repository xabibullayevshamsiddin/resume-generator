# Ma'lumotnoma generatori

Rasmiy ma'lumotnoma (rezyume) PDF/DOCX generatori — Laravel web + REST API + Flutter mobil ilova. (Resume / Rezyume Generator)

Foydalanuvchi formada shaxsiy ma'lumotlarini, ta'limi, mehnat faoliyati va yaqin qarindoshlari haqidagi ma'lumotlarni kiritadi, 3x4 profil rasmini yuklaydi va **bitta tugma bosish orqali** rasmiy ko'rinishdagi ikki sahifali A4 PDF ma'lumotnomani yuklab oladi.

**Maxfiylik:** agar "Ma'lumotnomani bazaga saqlash" belgilanmasa — hech qanday login/database yozuvi yo'q: telefon, manzil, pasport ma'lumotlari faqat bitta request davomida qayta ishlanadi va PDF qaytarilgach yo'q bo'lib ketadi.

**Bazaga saqlash (ixtiyoriy):** forma pastidagi "Ma'lumotnomani bazaga saqlash" katakchasi belgilansa, ma'lumotnoma `resumes` jadvaliga saqlanadi — **maxfiy maydonlar (telefon, manzil, pasport, qarindoshlar, mehnat faoliyati) Laravel encrypted cast bilan shifrlangan holda**. Rasm ham faqat private `local` diskda saqlanadi. `/resumes` sahifasidan ro'yxatni ko'rish, qayta PDF yaratish, o'chirish mumkin.

## Texnologiyalar

- Laravel 9.x (composer.json'dagi mavjud loyiha asosida) — PHP **8.1** minimal, PHP 8.2 tavsiya etiladi
- Blade, Vanilla JavaScript, oddiy CSS, **Laravel Mix** (Vite emas — Laravel 9 standarti)
- `barryvdh/laravel-dompdf` (dompdf 3.x)
- MySQL (saqlash funksiyasi uchun; standart: `DB_CONNECTION=mysql`) — testlar in-memory SQLite'da
- PHPUnit

## Talablar

- PHP >= 8.1, extension'lar: `dom`, `mbstring`, `gd` (yoki imagick), `fileinfo`, `openssl`, `xml`
- Node.js >= 16 (npm)
- Composer

## O'rnatish

```bash
composer install
npm install

# .env faylini tayyorlash (agar bo'lmasa)
cp .env.example .env
php artisan key:generate
```

`.env`da alohida sozlash talab qilinmaydi: database, mail va boshqa xizmatlar ishlatilmaydi. Fayllar faqat standart `local` diskka (storage/app) yoziladi.

DomPDF sozlamalari `config/dompdf.php`da (publish qilingan). Xavfsizlik uchun:

- `'enable_php' => false` — HTML ichida PHP execution o'chirilgan
- `'enable_remote' => false` — remote URL'lardan rasm/style yuklash o'chirilgan
- `'chroot' => realpath(base_path())` — dompdf faqat loyiha papkasiga kiradi

PDF'dagi rasm `data:` URI sifatda inline uzatiladi, shuning uchun remote yuklash kerak emas.

## Ishga tushirish (development)

```bash
php artisan serve          # yoki OSPanel/OpenServer'da virtual host
php artisan migrate        # resumes jadvalini yaratish (saqlash uchun)
npm run dev                # assetlarni build qilish (bir marta)
# yoki
npm run watch              # o'zgarishlarni kuzatish
```

- Home (landing): `GET /` (route nomi `home`) — hero + video qo'llanma modali
- Forma: `GET /yarat` (route nomi `resume.form`)
- PDF: `POST /resume/pdf` (route nomi `resume.pdf`, `throttle:10,1`)
- Formatlar: forma `format=pdf|docx` — Word versiyasi `phpoffice/phpword` bilan yaratiladi (Times New Roman, A4, rasm bilan)
- Saqlangan yozuvdan: `GET /resumes/{id}/pdf` (PDF) · `GET /resumes/{id}/pdf?format=docx` (Word)
- Ro'yxat: `GET /resumes` · Ko'rish: `GET /resumes/{id}` · Qayta PDF: `GET /resumes/{id}/pdf` · O'chirish: `DELETE /resumes/{id}`

## Mobil ilova (Flutter) — API

Backend'da mobil ilova uchun yagona ochiq endpoint mavjud:

```
POST /api/v1/resume/pdf    # multipart, web formasi bilan BIR XIL maydon formati
                            # (employment[0][period], photo, ...), throttle:10,1
```

- Validatsiya xatolari — `422` JSON (`errors` obyekti, maydon nomlari bo'yicha)
- **Xavfsizlik:** saqlangan ma'lumotnomalar ro'yxatini qaytaruvchi API endpoint ATAYIN YO'Q — pasport/telefon/manzillar maxfiy. Kelajakda kerak bo'lsa — sanctum token auth bilan alohida bosqichda.
- Flutter manba kodi `mobile/` papkasida — build va batafsil ma'lumot uchun **[mobile/README.md](mobile/README.md)**
- **APK yuklab olish (Flutter kerak emas):** har push'da GitHub Actions APK build qilib [Releases](https://github.com/xabibullayevshamsiddin/resume-generator/releases/latest) sahifasiga qo'yadi — `app-release.apk` ni yuklab olib telefonga o'rnating. Ilova birinchi ochilishda server manzilini so'raydi (⚙ orqali keyin ham o'zgartiriladi).

```bash
# APK'ni qo'lda yig'ish (Flutter SDK bo'lgan mashinada):
cd mobile
flutter pub get
flutter build apk --release --dart-define=API_BASE_URL=https://sizning-domeningiz.uz
```
> **Video qo'llanma:** `public/videos/qollanma.mp4` faylini joylashtirsangiz, home sahifadagi "Video qo'llanmani ko'rish" tugmasi avtomatik faollashadi (fayl bo'lmasa tugma disabled holatda turadi, xato bermaydi).

> **Subpapka/nginx rewrite muammosi:** rewrite qilmaydigan serverlarda ichki havolalar `index.php?_route=/...` formatida ishlaydi (`public/index.php` shim + `resume_route()` helperi). Rewrite ishlaydigan serverda bu shaffof.

## Build

```bash
npm run build   # mix --production, public/ ga versioned assetlar yozadi
```

## Testlar

```bash
php artisan test
```

Majburiy testlar: forma sahifasi, CSRF, majburiy maydonlar validatsiyasi, rasm turi/hajmi, employment/relatives array limitlari, `application/pdf` javobi, xavfsiz filename, vaqtinchalik rasmning o'chirilishi, maxfiy ma'lumotlarning logga tushmasligi, rate limit.

## Production deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Talablar:

- PHP 8.2 (extension'lar yuqorida), `storage/` papkasiga yozish ruxsati
- `APP_DEBUG=false`, `APP_ENV=production`
- Web-server (php.ini): `upload_max_filesize` va `post_max_size` kamida **4M** (3MB rasm + forma maydonlari)

## O'zbek lotin harflari (font)

PDF shablonida `DejaVu Sans` ishlatilgan — dompdf bilan birga keladi va o'zbek lotin harflarini (`o'`, `g'`, `ʻ`, `—`) to'g'ri ko'rsatadi. Boshqa font kerak bo'lsa: TTF faylni `storage/fonts/`ga joylang va CSS'da `@font-face` orqali ulang (dompdf `chroot` ichidagi fayllarni o'qiy oladi).

## Loyiha tuzilmasi

```
app/
├── Http/Controllers/ResumeController.php     # GET forma, POST PDF download
├── Http/Requests/GenerateResumeRequest.php   # validatsiya (o'zbekcha xabarlar)
├── Services/ResumePdfService.php             # PDF mantıq'i, helperlar
└── Support/helpers.php                       # frontend_asset() helperi
resources/
├── css/{app,home,resume-form,resume-pdf}.css
├── js/{app,home,resume-form}.js              # home: video modal; resume-form: dinamik qatorlar, preview, submit guard
└── views/
    ├── layouts/{app,home}.blade.php
    ├── home.blade.php                        # landing sahifa (hero + video modal)
    └── resume/
        ├── form.blade.php
        ├── pdf.blade.php                     # DomPDF wrapper (CSS inline)
        └── partials/
            ├── document-content.blade.php    # hujjat strukturasi (preview + PDF umumiy)
            ├── employment-row.blade.php
            └── relative-row.blade.php
routes/web.php
tests/Feature/ResumePdfTest.php
tests/Unit/ResumePdfServiceTest.php
```

## Maxfiylik (qisqacha)

1. CSRF himoyasi (`@csrf` + `web` middleware guruhi)
2. Blade'da barcha qiymatlar escaped (`{{ }}`), xom HTML yo'q; JS preview'da ham `escapeHtml()`
3. Pasport/telefon/manzil hech qachon loglanmaydi, request payload logga yozilmaydi
4. Vaqtinchalik rasm faqat `Storage::disk('local')`da, MIME turi serverda tekshiriladi, nomi UUID bilan generatsiya qilinadi
5. Rasm `try/finally` blokida o'chiriladi — xatolikda ham qolib ketmaydi
6. PDF serverda saqlanmaydi — faqat download response
7. `throttle:10,1` rate limit; DomPDF'da remote yuklash va PHP execution o'chirilgan
