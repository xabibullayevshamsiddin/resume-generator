import 'dart:io' show Platform;

/// API manzili build vaqtida beriladi:
///   flutter build apk --release --dart-define=API_BASE_URL=https://domen.uz
///
/// Berilmasa — lokal dev uchun standart (Android emulyatorda 10.0.2.2 = host).
const String kApiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://10.0.2.2:8000',
);

/// Serverdagi video qo'llanma manzili (ilova ichiga o'rnatilmaydi —
/// bitta joyda yangilansa hammasiga yetadi).
String videoUrl() {
  final base = Uri.parse(kApiBaseUrl);
  final isAndroidEmulator = Platform.isAndroid && base.host == '10.0.2.2';

  return base.replace(path: '/videos/qollanma.mp4').toString();
}
