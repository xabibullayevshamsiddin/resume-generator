import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

import '../core/theme.dart';
import '../widgets/seal_animation.dart';
import 'form_screen.dart';
import 'video_screen.dart';

/// Veb landing sahifasining mobil ko'rinishi:
/// Ink fon, markazda serif sarlavha, brass CTA + kontur video tugma,
/// orqa fonda sekin aylanuvchi muhr.
class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
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
                    const SizedBox(height: 48),
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
