import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

/// Veb versiyadagi rang tokenlari bilan bir xil (home.css bilan mos):
/// Ink — fon, Parchment — matn, Brass — urg'u, Slate — ikkinchi darajali.
class AppColors {
  AppColors._();

  static const Color ink = Color(0xFF132743);
  static const Color parchment = Color(0xFFEDE6D6);
  static const Color brass = Color(0xFFC9A227);
  static const Color slate = Color(0xFF8093AC);

  /// Forma ekranlari foni — vebdagidek och qog'oz.
  static const Color paper = Color(0xFFFAFAF7);

  static const Color error = Color(0xFFB3261E);
}

/// Sarlavhalar — "Lora" serif (veb bilan bir xil), matn — system sans-serif.
class AppTheme {
  AppTheme._();

  static ThemeData get dark => ThemeData(
        useMaterial3: true,
        scaffoldBackgroundColor: AppColors.ink,
        colorScheme: const ColorScheme.dark(
          primary: AppColors.brass,
          onPrimary: AppColors.ink,
          secondary: AppColors.parchment,
          onSecondary: AppColors.ink,
          surface: AppColors.ink,
          onSurface: AppColors.parchment,
          error: AppColors.error,
        ),
        textTheme: TextTheme(
          displaySmall: GoogleFonts.lora(
            color: AppColors.parchment,
            fontWeight: FontWeight.w600,
          ),
          bodyMedium: const TextStyle(color: AppColors.parchment),
          bodySmall: const TextStyle(color: AppColors.slate),
        ),
        filledButtonTheme: FilledButtonThemeData(
          style: FilledButton.styleFrom(
            backgroundColor: AppColors.brass,
            foregroundColor: AppColors.ink,
            textStyle: const TextStyle(fontWeight: FontWeight.w600),
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            foregroundColor: AppColors.parchment,
            side: const BorderSide(color: AppColors.parchment),
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.white,
          labelStyle: const TextStyle(color: AppColors.ink),
          hintStyle: TextStyle(color: Colors.grey.shade500),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(color: Colors.grey.shade400),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(color: Colors.grey.shade400),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: const BorderSide(color: AppColors.brass, width: 2),
          ),
          errorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: const BorderSide(color: AppColors.error, width: 2),
          ),
          focusedErrorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: const BorderSide(color: AppColors.error, width: 2),
          ),
          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        ),
        snackBarTheme: const SnackBarThemeData(
          backgroundColor: AppColors.parchment,
          contentTextStyle: TextStyle(color: AppColors.ink),
          behavior: SnackBarBehavior.floating,
        ),
      );
}
