import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

import '../core/constants.dart';
import '../core/theme.dart';

/// Rasm tanlash vidjeti: kamera yoki galereya, preview, hajm/format tekshiruvi.
///
/// Backend'dagi kabi 3MB va jpg/jpeg/png/webp cheklovlari bu yerda ham
/// tekshiriladi — foydalanuvchi server xatosini kutib qolmaydi.
class PhotoPicker extends StatelessWidget {
  const PhotoPicker({
    super.key,
    required this.photo,
    required this.onPicked,
    required this.onError,
  });

  /// Tanlangan fayl (null — hali tanlanmagan).
  final File? photo;

  /// Yangi fayl tanlanganda chaqiriladi.
  final ValueChanged<File> onPicked;

  /// Tekshiruv xatosi (hajm/format) uchun xabar.
  final ValueChanged<String> onError;

  Future<void> _pick(ImageSource source) async {
    final picker = ImagePicker();

    try {
      final picked = await picker.pickImage(
        source: source,
        maxWidth: 1200,
        maxHeight: 1600,
        imageQuality: 88,
      );

      if (picked == null) {
        return;
      }

      final file = File(picked.path);
      final ext = picked.path.split('.').last.toLowerCase();

      if (!AppConstants.allowedPhotoExtensions.contains(ext)) {
        onError('Rasm faqat jpg, jpeg, png yoki webp formatida bo\'lishi kerak.');
        return;
      }

      final size = await file.length();

      if (size > AppConstants.photoMaxBytes) {
        onError('Rasm 3 MB dan oshmasligi kerak.');
        return;
      }

      onPicked(file);
    } catch (_) {
      onError('Rasm tanlashda xatolik yuz berdi.');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Profil rasmi (3×4) *',
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                fontWeight: FontWeight.w600,
              ),
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            // Preview: 3×4 nisbatda
            Container(
              width: 84,
              height: 112,
              decoration: BoxDecoration(
                color: Colors.grey.shade200,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: Colors.grey.shade400),
              ),
              clipBehavior: Clip.antiAlias,
              child: photo != null
                  ? Image.file(photo!, fit: BoxFit.cover)
                  : Icon(Icons.person, size: 40, color: Colors.grey.shade500),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  OutlinedButton.icon(
                    onPressed: () => _pick(ImageSource.gallery),
                    icon: const Icon(Icons.photo_library_outlined),
                    label: const Text('Galereyadan tanlash'),
                  ),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: () => _pick(ImageSource.camera),
                    icon: const Icon(Icons.photo_camera_outlined),
                    label: const Text('Kameradan olish'),
                  ),
                ],
              ),
            ),
          ],
        ),
      ],
    );
  }
}

/// Sho'rt xato xabari (rasm tekshiruvi uchun).
class PhotoErrorText extends StatelessWidget {
  const PhotoErrorText({super.key, required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 6),
      child: Text(
        message,
        style: const TextStyle(color: AppColors.error, fontSize: 12),
      ),
    );
  }
}
