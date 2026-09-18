# Ma'lumotnoma Generatori — Mobil Ilova (Flutter)

Laravel backend'dagi `POST /api/v1/resume/pdf` endpointiga ulanib, rasmiy
ma'lumotnoma PDF yaratadigan Android/iOS ilova.

## Build (APK)

```bash
cd mobile
flutter pub get

# Lokal test (emulyator backend'ga 10.0.2.2 orqali ulanadi — host kompyuter):
flutter build apk --release --dart-define=API_BASE_URL=http://10.0.2.2:8000

# Production (HTTPS majburiy):
flutter build apk --release --dart-define=API_BASE_URL=https://sizning-domeningiz.uz
```

Natija: `build/app/outputs/flutter-apk/app-release.apk`

## Muhim eslatmalar

- **API manzili** faqat build vaqtida beriladi (`--dart-define`), ilova
  ichiga yozib qo'yilmaydi.
- **HTTPS** productionda majburiy — Android 9+ cleartext HTTP ni bloklaydi.
  Lokal testda `10.0.2.2` uchun `AndroidManifest.xml`da
  `android:usesCleartextTraffic` emas, `network_security_config` orqali
  faqat ruxsat berilgan domen ochiladi.
- Backend `throttle:10,1` — 1 daqiqada 10 so'rov. Ko'proq yuborilsa 429.
- Backend validatsiya xatolari (422) maydon nomlari bo'yicha ilovada
  ko'rsatiladi — yakuniy haqiqat manbai server.

## Backend (shu repoda)

- `POST /api/v1/resume/pdf` — validatsiya + PDF binary (web formadagi bilan
  bir xil multipart maydon formati: `employment[0][period]`, `photo`, ...)
- Ro'yxat/ko'rish API endpointlari ATAYIN yo'q (maxfiylik — pasport/telefon).
