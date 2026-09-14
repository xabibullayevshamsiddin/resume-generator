# Ma'lumotnoma generatori (Resume / Rezyume Generator)

Foydalanuvchi formada shaxsiy ma'lumotlarini, ta'limi, mehnat faoliyati va yaqin qarindoshlari haqidagi ma'lumotlarni kiritadi, 3x4 profil rasmini yuklaydi va **bitta tugma bosish orqali** rasmiy ko'rinishdagi ikki sahifali A4 PDF ma'lumotnomani yuklab oladi.

**Maxfiylik:** hech qanday login/database yo'q. Telefon, manzil, pasport ma'lumotlari faqat bitta request davomida qayta ishlanadi, PDF qaytarilgach yo'q bo'lib ketadi. Vaqtinchalik rasm faqat `local` diskda (storage/app) saqlanadi va PDF generatsiyasidan so'ng darhol (`try/finally`) o'chiriladi — `public` disk hech qachon ishlatilmaydi.

## Texnologiyalar

- Laravel 9.x (composer.json'dagi mavjud loyiha asosida) — PHP **8.1** minimal, PHP 8.2 tavsiya etiladi
- Blade, Vanilla JavaScript, oddiy CSS, **Laravel Mix** (Vite emas — Laravel 9 standarti)
- `barryvdh/laravel-dompdf` (dompdf 3.x)
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
npm run dev                # assetlarni build qilish (bir marta)
# yoki
npm run watch              # o'zgarishlarni kuzatish
```

- Forma: `GET /` (route nomi `resume.form`)
- PDF: `POST /resume/pdf` (route nomi `resume.pdf`, `throttle:10,1`)

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
├── css/{app,resume-form,resume-pdf}.css
├── js/{app,resume-form}.js                   # dinamik qatorlar, preview, submit guard
└── views/
    ├── layouts/app.blade.php
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
