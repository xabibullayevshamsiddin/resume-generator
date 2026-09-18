import 'package:flutter/material.dart';

import '../core/constants.dart';
import '../core/theme.dart';
import '../models/form_entries.dart';

/// Bitta qarindosh qatori — veb formadagi jadval ustunlari bilan bir xil:
/// qarindoshlik (+ izoh "Boshqa" uchun), F.I.Sh., tug'ilgan yili/joyi,
/// ish joyi, lavozim, manzil, telefon.
class RelativeRow extends StatelessWidget {
  const RelativeRow({
    super.key,
    required this.entry,
    required this.index,
    required this.onRemove,
    required this.canRemove,
    required this.onChanged,
  });

  final RelativeEntry entry;
  final int index;
  final VoidCallback onRemove;
  final bool canRemove;

  /// Dropdown "Boshqa"ga o'tganda ota-ekran setState uchun.
  final VoidCallback onChanged;

  @override
  Widget build(BuildContext context) {
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
                    '${index + 1}-qarindosh',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                          color: AppColors.ink,
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
            DropdownButtonFormField<String>(
              initialValue: entry.relationship,
              decoration: const InputDecoration(labelText: 'Qarindoshligi *'),
              items: AppConstants.relationshipOptions
                  .map((v) => DropdownMenuItem(value: v, child: Text(v)))
                  .toList(),
              onChanged: (v) {
                entry.relationship = v ?? entry.relationship;
                onChanged();
              },
            ),
            if (entry.isOtherRelationship) ...[
              const SizedBox(height: 10),
              TextFormField(
                initialValue: entry.relationshipOther,
                decoration: const InputDecoration(
                  labelText: 'Qarindoshlik darajasi (izoh) *',
                  hintText: 'Masalan: Amakivachcha',
                ),
                onChanged: (v) => entry.relationshipOther = v,
              ),
            ],
            const SizedBox(height: 10),
            TextFormField(
              initialValue: entry.fullName,
              decoration: const InputDecoration(labelText: "F.I.Sh. *"),
              textInputAction: TextInputAction.next,
              onChanged: (v) => entry.fullName = v,
            ),
            const SizedBox(height: 10),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    initialValue: entry.birthYear,
                    decoration: const InputDecoration(labelText: "Tug'ilgan yili"),
                    keyboardType: TextInputType.number,
                    textInputAction: TextInputAction.next,
                    onChanged: (v) => entry.birthYear = v,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: TextFormField(
                    initialValue: entry.birthPlace,
                    decoration: const InputDecoration(labelText: "Tug'ilgan joyi"),
                    textInputAction: TextInputAction.next,
                    onChanged: (v) => entry.birthPlace = v,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            TextFormField(
              initialValue: entry.workplace,
              decoration: const InputDecoration(labelText: 'Ish joyi'),
              textInputAction: TextInputAction.next,
              onChanged: (v) => entry.workplace = v,
            ),
            const SizedBox(height: 10),
            TextFormField(
              initialValue: entry.position,
              decoration: const InputDecoration(labelText: 'Lavozimi'),
              textInputAction: TextInputAction.next,
              onChanged: (v) => entry.position = v,
            ),
            const SizedBox(height: 10),
            TextFormField(
              initialValue: entry.address,
              decoration: const InputDecoration(labelText: 'Turar joy manzili'),
              textInputAction: TextInputAction.next,
              onChanged: (v) => entry.address = v,
            ),
            const SizedBox(height: 10),
            TextFormField(
              initialValue: entry.phone,
              decoration: const InputDecoration(labelText: 'Telefon raqami'),
              keyboardType: TextInputType.phone,
              onChanged: (v) => entry.phone = v,
            ),
          ],
        ),
      ),
    );
  }
}
