import 'package:flutter/material.dart';

import 'core/settings.dart';
import 'core/theme.dart';
import 'screens/home_screen.dart';

void main() {
  runApp(const ResumeGeneratorApp());
}

class ResumeGeneratorApp extends StatelessWidget {
  const ResumeGeneratorApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: "Ma'lumotnoma generatori",
      debugShowCheckedModeBanner: false,
      theme: AppTheme.dark,
      home: const _HomeGate(),
    );
  }
}

/// Ilovani ochish darvozasi: server manzili hali sozlanmagan bo'lsa
/// (standart `https://localhost` qolib ketgan bo'lsa) birinchi ochilishda
/// Sozlamalar oynasi ochiladi — foydalanuvchi haqiqiy manzilni kiritsa,
/// so'rovlar to'g'ri serverga yuboriladi.
class _HomeGate extends StatefulWidget {
  const _HomeGate();

  @override
  State<_HomeGate> createState() => _HomeGateState();
}

class _HomeGateState extends State<_HomeGate> {
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    // AppSettings.apiBaseUrl() keshni ham yangilaydi (ApiClient shu keshdan o'qiydi).
    final url = await AppSettings.apiBaseUrl();
    final needsSetup = Uri.parse(url).host == 'localhost';

    if (!mounted) {
      return;
    }

    if (needsSetup) {
      // Foydalanuvchi bekor qilsa ham ilova ochiladi — so'rov xatolari
      // tushunarli ko'rsatiladi va Home'dagi ⚙ orqali qayta sozlash mumkin.
      await _openSettings(url);
    }

    if (!mounted) {
      return;
    }

    setState(() => _loading = false);
  }

  Future<void> _openSettings(String current) {
    return showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      isDismissible: false,
      enableDrag: false,
      backgroundColor: AppColors.ink,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (_) => Padding(
        padding:
            EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
        child: SettingsScreen(currentBaseUrl: current),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator(color: AppColors.brass)),
      );
    }

    return const HomeScreen();
  }
}
