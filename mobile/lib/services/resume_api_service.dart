import 'dart:io';

import 'package:dio/dio.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';
import 'package:share_plus/share_plus.dart';

import '../core/api_client.dart';

/// PDF generatsiya natijasi.
class PdfResult {
  const PdfResult({required this.file});

  final File file;
}

/// Ma'lumotnoma xizmati: backend'ga multipart POST yuboradi,
/// qaytgan PDF baytlarini qurilmaga saqlaydi.
class ResumeApiService {
  ResumeApiService({Dio? dio}) : _dio = dio ?? ApiClient.create();

  final Dio _dio;

  /// Backend'ga to'liq formani yuborib, PDF faylni qaytaradi.
  ///
  /// Multipart maydon formati veb formasi bilan AYNAN bir xil:
  /// `employment[0][period]`, `relatives[0][full_name]`, `photo` va h.k.
  /// Backend 422 qaytarsa — maydon nomlari bo'yicha xatolar olinadi.
  Future<PdfResult> generatePdf({
    required Map<String, String> fields,
    required Map<String, List<String>> arrayFields,
    required File? photo,
  }) async {
    final formData = FormData();

    // Oddiy maydonlar
    fields.forEach((key, value) =>
        formData.fields.add(MapEntry<String, String>(key, value)));

    // Nested array maydonlar: employment[0][period] kabi
    arrayFields.forEach((key, values) {
      for (final value in values) {
        formData.fields.add(MapEntry(key, value));
      }
    });

    if (photo != null) {
      final fileName = photo.path.split(Platform.pathSeparator).last;
      formData.files.add(MapEntry(
        'photo',
        await MultipartFile.fromFile(photo.path, filename: fileName),
      ));
    }

    final response = await _dio.post(
      '/api/v1/resume/pdf',
      data: formData,
    );

    final status = response.statusCode ?? 0;

    if (status == 422) {
      // Validatsiya xatolari — maydon nomlari bo'yicha JSON
      throw ValidationException.fromResponse(response.data);
    }

    if (status == 429) {
      throw const ApiException("Juda ko'p so'rov yuborildi. Bir daqiqa kutib turing.");
    }

    if (status != 200) {
      throw ApiException('Server xatosi ($status). Keyinroq qayta urinib ko\'ring.');
    }

    final bytes = response.data;

    if (bytes is! List<int>) {
      throw const ApiException('Serverdan noto\'g\'ri javob keldi.');
    }

    // PDF imzosini tekshirish (backend'dan toza kelganini tasdiqlash)
    final head = String.fromCharCodes(bytes.take(4));
    if (!head.startsWith('%PDF')) {
      throw const ApiException('Serverdan PDF o\'rniga boshqa fayl keldi.');
    }

    final saved = await _savePdf(bytes);

    return PdfResult(file: saved);
  }

  /// PDF baytlarini qurilmaning `Downloads`-uslubidagi papkasiga saqlaydi.
  Future<File> _savePdf(List<int> bytes) async {
    final dir = await getApplicationDocumentsDirectory();

    final stamp = DateTime.now().millisecondsSinceEpoch;
    final path = '${dir.path}/malumotnoma-$stamp.pdf';

    return File(path).writeAsBytes(bytes, flush: true);
  }

  /// PDF'ni tashqi ilovada ochadi (PDF o'quvchi, brauzer...).
  /// Natija `OpenResult` — form ekranida muvaffaqiyat tekshiriladi.
  static Future<OpenResult> openPdf(String path) {
    return OpenFilex.open(path);
  }

  /// PDF'ni ulashish (Telegram, WhatsApp, pochta...).
  static Future<void> sharePdf(String path) async {
    await Share.shareXFiles([XFile(path)], text: "Ma'lumotnoma");
  }
}

/// Umumiy API xatosi (foydalanuvchiga ko'rsatiladigan xabar bilan).
class ApiException implements Exception {
  const ApiException(this.message);

  final String message;

  @override
  String toString() => message;
}

/// Validatsiya xatosi: maydon nomi → xabar xaritasi.
class ValidationException implements Exception {
  ValidationException(this.errors);

  /// Masalan: {'full_name': ['F.I.Sh. kiritilishi shart.'], ...}
  final Map<String, List<String>> errors;

  factory ValidationException.fromResponse(dynamic data) {
    final map = <String, List<String>>{};

    if (data is Map) {
      final rawErrors = data['errors'];

      if (rawErrors is Map) {
        rawErrors.forEach((key, value) {
          if (value is List) {
            map[key.toString()] = value.map((e) => e.toString()).toList();
          }
        });
      }
    }

    return ValidationException(map);
  }

  /// Barcha xatolarni bitta o'qiladigan ro'yxatga yig'adi.
  List<String> allMessages() {
    final messages = <String>[];

    errors.forEach((_, list) => messages.addAll(list));

    return messages;
  }
}
