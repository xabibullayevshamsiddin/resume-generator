import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import 'api_config.dart';
import 'theme.dart';

/// Qurilma xotirasida saqlanadigan sozlamalar (API manzili).
/// flutter_secure_storage — Android'da EncryptedSharedPreferences/Keystore:
/// server manzili ham maxfiy ma'lumot hisoblanadi, oddiy SharedPreferences
/// o'rniga shifrlangan xotira ishlatiladi.
class AppSettings {
  AppSettings._();

  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );

  static const _apiBaseUrlKey = 'api_base_url';

  /// Oxirgi o'qilgan manzil (sinxron o'qish uchun — Dio har so'rovda
  /// async storage'ga tegmasligi kerak). [apiBaseUrl] to'ldiradi.
  static String _cachedBaseUrl = kDefaultApiBaseUrl;

  static String get cachedBaseUrl => _cachedBaseUrl;

  /// Saqlangan manzil, bo'lmasa build-vaqtidagi standart.
  /// Har o'qishda sinxron kesh ham yangilanadi.
  static Future<String> apiBaseUrl() async {
    final saved = await _storage.read(key: _apiBaseUrlKey);

    _cachedBaseUrl = (saved == null || saved.trim().isEmpty)
        ? kDefaultApiBaseUrl
        : saved.trim();

    return _cachedBaseUrl;
  }

  static Future<void> setApiBaseUrl(String url) async {
    _cachedBaseUrl = url.trim();

    return _storage.write(key: _apiBaseUrlKey, value: url.trim());
  }
}

/// ⚙ Sozlamalar ekrani — server manzilini kiriting/tekshiring.
/// Birinchi ochilishda (manzil sozlanmagan bo'lsa) ilova shu ekranni ko'rsatadi.
class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key, required this.currentBaseUrl});

  final String currentBaseUrl;

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  late final TextEditingController _url =
      TextEditingController(text: widget.currentBaseUrl);

  bool _saving = false;

  @override
  void dispose() {
    _url.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final raw = _url.text.trim();

    if (raw.isEmpty) {
      _snack('Server manzili bosh bolishi mumkin emas.', isError: true);
      return;
    }

    final uri = Uri.tryParse(raw);

    if (uri == null ||
        (!uri.isScheme('http') && !uri.isScheme('https')) ||
        uri.host.isEmpty) {
      _snack(
        'Manzil http:// yoki https:// bilan boshlanishi kerak.\n'
        'Masalan: http://10.64.199.31',
        isError: true,
      );
      return;
    }

    setState(() => _saving = true);

    try {
      await AppSettings.setApiBaseUrl(raw);

      if (!mounted) {
        return;
      }

      Navigator.of(context).pop(raw);
      _snack('Saqlandi ✅');
    } finally {
      if (mounted) {
        setState(() => _saving = false);
      }
    }
  }

  void _snack(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(message),
      backgroundColor: isError ? AppColors.error : null,
    ));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.ink,
      appBar: AppBar(
        backgroundColor: AppColors.ink,
        foregroundColor: AppColors.parchment,
        title: const Text('Sozlamalar'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text(
            'Server manzili',
            style: TextStyle(
              color: AppColors.parchment,
              fontWeight: FontWeight.w600,
              fontSize: 15,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            "Ma'lumotnoma generatori backend manzili. "
            "Kompyuter bilan bir Wi-Fi'da bolsangiz, kompyuterning "
            'lokal IP manzilini kiriting (masalan http://10.64.199.31).',
            style: TextStyle(color: AppColors.slate, fontSize: 13),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _url,
            keyboardType: TextInputType.url,
            autocorrect: false,
            enableSuggestions: false,
            style: const TextStyle(color: AppColors.ink),
            decoration: const InputDecoration(
              hintText: 'http://10.64.199.31 yoki https://domen.uz',
            ),
          ),
          const SizedBox(height: 16),
          FilledButton(
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Text('Saqlash'),
          ),
          const SizedBox(height: 12),
          OutlinedButton(
            onPressed: () => Navigator.of(context).pop(null),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: AppColors.slate),
              foregroundColor: AppColors.slate,
            ),
            child: const Text('Bekor qilish'),
          ),
        ],
      ),
    );
  }
}
