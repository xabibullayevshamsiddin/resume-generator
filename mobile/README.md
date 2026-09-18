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

## Reklama (AdMob) — pul ishlash

Ilovada AdMob banner (Home pastida) va interstitial (har 3-PDF'dan keyin) bor.
Hozir **Google TEST ID'lari** ishlatilgan — reklama ko'rinadi, lekin **daromad bermaydi**.

Real daromad uchun:
1. [apps.admob.com](https://apps.admob.com) da ilova qo'shing (package: `com.example.resume_generator`)
2. `mobile/android/app/src/main/AndroidManifest.xml`da test APP ID'ni (`ca-app-pub-3940256099942544~3347511713`) o'z ID'ingizga almastiring
3. `mobile/lib/core/ads.dart`da banner va interstitial unit ID'larini o'zingiznikiga o'zgartiring
4. Push qiling — CI yangi APK build qiladi

⚠️ **Muhim:** AdMob siyosati bo'yicha o'zingizning ilovalaringizga o'zi bosish
(yoki boshqalarga buyurish) hisobni doimiy bloklaydi. Test qurilmalaringizni
AdMob konsolida ro'yxatdan o'tkazing. Shuningdek, foydalanuvchi shaxsiy
ma'lumotlari (pasport, telefon) serverga yuborilishi bilan bog'liq ilovada
GDPR/izoh talablari paydo bo'lishi mumkin — AdMob UMP consent formasi
talab qilinsa `google_mobile_ads` hujjatidagi Consent API qo'shing.

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
