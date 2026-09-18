import 'package:dio/dio.dart';

import 'api_config.dart';

/// Dio sozlamalari: bazaviy URL, timeout, xatolarni yagona ko'rinishga keltirish.
class ApiClient {
  ApiClient._();

  static Dio create() {
    final dio = Dio(BaseOptions(
      baseUrl: kApiBaseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(minutes: 2),
      sendTimeout: const Duration(minutes: 2),
      // PDF binary qaytadi — Dio avtomatik parse qilmasin.
      responseType: ResponseType.bytes,
      validateStatus: (status) => status != null && status < 500,
    ));

    dio.interceptors.add(InterceptorsWrapper(
      onError: (e, handler) {
        handler.next(e);
      },
    ));

    return dio;
  }

  /// Dio xatosini foydalanuvchiga tushunarli o'zbekcha xabarga aylantiradi.
  static String friendlyError(Object error) {
    if (error is DioException) {
      final type = error.type;

      if (type == DioExceptionType.connectionError ||
          type == DioExceptionType.connectionTimeout ||
          type == DioExceptionType.receiveTimeout) {
        return 'Internetga ulanish yo\'q. Ulanishni tekshirib, qayta urinib ko\'ring.';
      }

      final code = error.response?.statusCode;

      if (code == 429) {
        return 'Juda ko\'p so\'rov yuborildi. Bir daqiqa kutib turing.';
      }

      if (code != null && code >= 500) {
        return 'Serverda xatolik. Keyinroq qayta urinib ko\'ring.';
      }

      return 'So\'rov bajarilmadi (${code ?? 'ulanish xatosi'}).';
    }

    return 'Kutilmagan xatolik yuz berdi.';
  }
}
