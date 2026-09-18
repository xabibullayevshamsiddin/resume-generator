/// Veb formasi bilan AYNAN bir xil konstantalar (select opsiyalari, limitlar).
///
/// Manba: resources/views/resume/form.blade.php va
/// app/Http/Requests/GenerateResumeRequest.php — o'zgartirilsa ikki joyda
/// sinxron bo'lishi shart.
class AppConstants {
  AppConstants._();

  /// Rasm hajmi limiti (bayt) — backend'dagi `max:3072` bilan bir xil.
  static const int photoMaxBytes = 3 * 1024 * 1024;

  static const int employmentMax = 20;
  static const int relativesMax = 10;

  /// Ruxsat etilgan rasm formatlari (backend `mimes:jpg,jpeg,png,webp`).
  static const List<String> allowedPhotoExtensions = ['jpg', 'jpeg', 'png', 'webp'];

  // ---- Select opsiyalari (veb bilan bir xil) ----

  static const String partyNo = "yo'q";
  static const String partyOther = 'boshqa';

  /// Partiyaviyligi: "yo'q" | "boshqa"
  static const List<String> partyAffiliationOptions = [partyNo, partyOther];

  /// Ma'lumoti: "o'rta" | "o'rta maxsus" | "oliy" | "tugallanmagan oliy"
  static const List<String> educationOptions = [
    "o'rta",
    "o'rta maxsus",
    'oliy',
    'tugallanmagan oliy',
  ];

  /// Ilmiy darajasi
  static const List<String> academicDegreeOptions = [
    partyNo,
    'bakalavr',
    'magistr',
    'PhD',
    'DSc',
    'boshqa',
  ];

  /// Ilmiy unvoni
  static const List<String> academicTitleOptions = [
    partyNo,
    'dotsent',
    'professor',
    'boshqa',
  ];

  /// Qarindoshlik darajasi (oxirgi — "Boshqa", tanlansa izoh maydoni ochiladi)
  static const List<String> relationshipOptions = [
    'Otasi',
    'Onasi',
    'Akasi',
    'Ukasi',
    'Opasi',
    'Singlisi',
    "Turmush o'rtog'i",
    "O'g'li",
    'Qizi',
    'Boshqa',
  ];

  static const String relationshipOther = 'Boshqa';
}
