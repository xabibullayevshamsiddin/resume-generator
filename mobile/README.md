# Ma'lumotnoma Generatori — Mobil Ilova (Flutter)

Laravel backend'dagi `POST /api/v1/resume/pdf` endpointiga ulanib, rasmiy
ma'lumotnoma PDF yaratadigan Android ilova.

## APK yuklab olish (Flutter o'rnatmasdan)

Har bir push'da **GitHub Actions** APK build qiladi va Releases sahifasiga
joylaydi:

1. Reponing **Actions** bo'limini oching — "Build Android APK" ishi yashil
   bo'lsa tayyor
2. Reponing o'ng tomonidagi **Releases** bo'limi → oxirgi release →
   `app-release.apk` ni yuklab oling
   (to'g'ridan-to'g'ri: `https://github.com/xabibullayevshamsiddin/resume-generator/releases/latest`)
3. APK'ni telefonda oching — "Noma'lum manbalardan o'rnatish"ga ruxsat bering
4. Ilova birinchi ochilganda **server manzilini** so'raydi:
   - Kompyuter bilan bir Wi-Fi: `http://<kompyuter-IP>` (masalan
     `http://10.64.199.31` — IP'ni `ipconfig` bilan ko'rish mumkin)
   - Production: `https://domeningiz.uz`
   - Keyinroq o'zgartirish: Home ekrandagi ⚙ tugma

Qo'lda build (masalan boshqa server manzili ichiga yozilgan APK kerak bo'lsa):
Actions → "Build Android APK" → **Run workflow** → API manzilini kiriting.

## Lokal build (Flutter SDK bo'lsa)

```bash
cd mobile
flutter pub get
flutter build apk --release --dart-define=API_BASE_URL=https://sizning-domeningiz.uz
```

Natija: `build/app/outputs/flutter-apk/app-release.apk`

## Muhim eslatmalar

- **Server manzili** ilova ichidagi sozlamalarda (shifrlangan xotira —
  flutter_secure_storage) saqlanadi; build-vaqtidagi `--dart-define`
  faqat standart qiymat sifatida xizmat qiladi.
- **HTTPS** productionda tavsiya etiladi — Android 9+ cleartext HTTP ni
  bloklaydi. Lokal IP'lar (10.0.2.2 emulyator, 10.64.199.31 Wi-Fi test)
  uchun `network_security_config.xml`da istisno berilgan. Boshqa HTTP
  manzil ishlatish kerak bo'lsa o'sha faylga qo'shing.
- Backend `throttle:10,1` — 1 daqiqada 10 so'rov. Ko'proq yuborilsa 429.
- Backend validatsiya xatolari (422) maydon nomlari bo'yicha ilovada
  ko'rsatiladi — yakuniy haqiqat manbai server.

## Backend (shu repoda)

- `POST /api/v1/resume/pdf` — validatsiya + PDF binary (web formadagi bilan
  bir xil multipart maydon formati: `employment[0][period]`, `photo`, ...)
- Ro'yxat/ko'rish API endpointlari ATAYIN yo'q (maxfiylik — pasport/telefon).
