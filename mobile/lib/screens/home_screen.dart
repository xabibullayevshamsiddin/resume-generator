import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../core/ads.dart';
import '../core/api_client.dart';
import '../core/settings.dart';
import '../core/theme.dart';
import '../widgets/seal_animation.dart';
import 'form_screen.dart';
import 'video_screen.dart';

/// Veb landing sahifasining mobil ko'rinishi:
/// Ink fon, markazda serif sarlavha, brass CTA + kontur video tugma,
/// orqa fonda sekin aylanuvchi muhr.
class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  Future<void> _openSettings(BuildContext context) async {
    final current = AppSettings.cachedBaseUrl;
    final saved = await showModalBottomSheet<String>(
      context: context,
      isScrollControlled: true,
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

    if (saved != null && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Server manzili saqlandi ✅')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // Pastda AdMob banner (yuklanmaganda joy egallamaydi)
      bottomNavigationBar: const AdBanner(),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        foregroundColor: AppColors.parchment,
        elevation: 0,
        actions: [
          IconButton(
            tooltip: 'Server sozlamalari',
            onPressed: () => _openSettings(context),
            icon: const Icon(Icons.settings_outlined),
          ),
        ],
      ),
      body: Stack(
        children: [
          // Orqa fondagi muhr (markazdan yuqoriroqda, vebdagidek)
          const Positioned(
            left: 0,
            right: 0,
            top: 0,
            bottom: 0,
            child: Center(
              child: SealAnimation(size: 380),
            ),
          ),
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // AppBar balandligini qoplaydigan bo'shliq (muhr markazda qolsin)
                    const SizedBox(height: 8),
                    // Sarlavha — "generatori" so'zi Brass + italic (vebdagidek)
                    Text.rich(
                      TextSpan(
                        children: [
                          const TextSpan(text: "Ma'lumotnoma\n"),
                          TextSpan(
                            text: 'generatori',
                            style: GoogleFonts.lora(
                              color: AppColors.brass,
                              fontStyle: FontStyle.italic,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                      textAlign: TextAlign.center,
                      style: GoogleFonts.lora(
                        fontSize: 34,
                        color: AppColors.parchment,
                        fontWeight: FontWeight.w600,
                        height: 1.2,
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      "Ma'lumotlaringizni kiriting va tayyor rasmiy hujjatni oling",
                      textAlign: TextAlign.center,
                      style: GoogleFonts.lora(
                        fontSize: 16,
                        color: AppColors.parchment.withOpacity(0.85),
                        height: 1.5,
                      ),
                    ),
                    const SizedBox(height: 40),
                    FractionallySizedBox(
                      widthFactor: 0.88,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          FilledButton(
                            onPressed: () => Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => const FormScreen()),
                            ),
                            child: const Text("Ma'lumotnoma yaratish"),
                          ),
                          const SizedBox(height: 14),
                          OutlinedButton(
                            onPressed: () => Navigator.of(context).push(
                              MaterialPageRoute(builder: (_) => const VideoScreen()),
                            ),
                            child: const Text(
                              "Video qo'llanmani ko'rish",
                              style: TextStyle(fontWeight: FontWeight.w500),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 48),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
