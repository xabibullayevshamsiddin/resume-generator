import 'package:flutter/material.dart';

import '../core/theme.dart';
import '../models/form_entries.dart';

/// Bitta mehnat faoliyati qatori (kartacha ko'rinishida).
/// Kiritilgan qiymatlar to'g'ridan-to'g'ri [entry] obyektiga yoziladi —
/// submit vaqtida ota-ekran shu obyektlardan formani yig'adi.
class EmploymentRow extends StatelessWidget {
  const EmploymentRow({
    super.key,
    required this.entry,
    required this.index,
    required this.onRemove,
    required this.canRemove,
  });

  final EmploymentEntry entry;
  final int index;
  final VoidCallback onRemove;
  final bool canRemove;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Card(
      color: Colors.white,
      margin: const EdgeInsets.only(bottom: 10),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${index + 1}-qator',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: AppColors.ink,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                if (canRemove)
                  IconButton(
                    tooltip: "O'chirish",
                    onPressed: onRemove,
                    icon: const Icon(Icons.delete_outline, color: AppColors.error),
                  ),
              ],
            ),
            TextFormField(
              initialValue: entry.period,
              decoration: const InputDecoration(
                labelText: 'Davri *',
                hintText: '2020 — 2024',
              ),
              textInputAction: TextInputAction.next,
              onChanged: (v) => entry.period = v,
            ),
            const SizedBox(height: 10),
            TextFormField(
              initialValue: entry.organization,
              decoration: const InputDecoration(
                labelText: 'Tashkilot nomi *',
                hintText: 'IT Park',
              ),
              textInputAction: TextInputAction.next,
              onChanged: (v) => entry.organization = v,
            ),
            const SizedBox(height: 10),
            TextFormField(
              initialValue: entry.position,
              decoration: const InputDecoration(
                labelText: 'Lavozim',
                hintText: 'Developer',
              ),
              textInputAction: TextInputAction.done,
              onChanged: (v) => entry.position = v,
            ),
          ],
        ),
      ),
    );
  }
}
