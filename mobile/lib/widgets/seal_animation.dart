import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../core/theme.dart';

/// Veb versiyadagi "muhr" shaklining Flutter'dagi ko'rinishi:
/// tashqi halqa + ichki halqa + radial chiziqlar (soat mexanizmi hissi).
///
/// Animatsiya 120 soniyada bitta to'liq aylanish — juda sekin, diqqatni
/// chalg'itmaydi. Tizimda "harakatlarni kamaytirish" yoqilgan bo'lsa
/// (`MediaQuery.disableAnimations`) muhr statik turadi.
class SealAnimation extends StatefulWidget {
  const SealAnimation({super.key, this.size = 320});

  final double size;

  @override
  State<SealAnimation> createState() => _SealAnimationState();
}

class _SealAnimationState extends State<SealAnimation>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();

    // 120s — veb versiyadagi `seal-rotate 120s linear infinite` bilan bir xil.
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 120),
    );
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();

    // prefers-reduced-motion ga mos qoida: animatsiya o'chiq bo'lsa muhr
    // aylanmaydi, faqat statik shakl ko'rinadi.
    final animationsEnabled = !MediaQuery.of(context).disableAnimations;

    if (animationsEnabled && !_controller.isAnimating) {
      _controller.repeat();
    } else if (!animationsEnabled && _controller.isAnimating) {
      _controller.stop();
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return Transform.rotate(
          angle: _controller.value * 2 * math.pi,
          child: child,
        );
      },
      child: CustomPaint(
        size: Size.square(widget.size),
        painter: _SealPainter(),
      ),
    );
  }
}

class _SealPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final outerRadius = size.width * 0.48;
    final innerRadius = size.width * 0.40;

    final ringPaint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = size.width * 0.012
      ..color = AppColors.brass;

    // Tashqi halqa
    canvas.drawCircle(center, outerRadius, ringPaint);

    // Ichki halqa
    canvas.drawCircle(center, innerRadius, ringPaint);

    // Radial chiziqlar (muhr "tishlari")
    final tickPaint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = size.width * 0.008
      ..color = AppColors.brass;

    const ticks = 48;

    for (var i = 0; i < ticks; i++) {
      final angle = (2 * math.pi / ticks) * i;
      final start = Offset(
        center.dx + math.cos(angle) * innerRadius,
        center.dy + math.sin(angle) * innerRadius,
      );
      final end = Offset(
        center.dx + math.cos(angle) * (outerRadius - size.width * 0.01),
        center.dy + math.sin(angle) * (outerRadius - size.width * 0.01),
      );

      canvas.drawLine(start, end, tickPaint);
    }

    // Markaziy doira
    canvas.drawCircle(center, size.width * 0.08, ringPaint);
  }

  @override
  bool shouldRepaint(covariant _SealPainter oldDelegate) => false;
}
