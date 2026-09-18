/// Build-vaqtidagi standart API manzili (`--dart-define=API_BASE_URL=...`).
/// CI'da `https://localhost` beriladi — bu haqiqiy server emas, shuning
/// uchun ilova birinchi ochilishda foydalanuvchidan haqiqiy manzilni so'raydi
/// (⚙ Sozlamalar, flutter_secure_storage'da saqlanadi).
const String kDefaultApiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'https://localhost',
);

/// Serverdagi video qo'llanma manzili (ilova ichiga o'rnatilmaydi —
/// bitta joyda yangilansa hammasiga yetadi).
String videoUrlFor(String baseUrl) {
  final base = Uri.parse(baseUrl);

  return base.replace(path: '/videos/qollanma.mp4').toString();
}
