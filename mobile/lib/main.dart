import 'package:flutter/material.dart';

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
      home: const HomeScreen(),
    );
  }
}
