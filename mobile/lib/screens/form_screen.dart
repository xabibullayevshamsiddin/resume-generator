import 'dart:io';

import 'package:flutter/material.dart';

import '../core/ads.dart';
import '../core/api_client.dart';
import '../core/constants.dart';
import '../core/theme.dart';
import '../models/form_entries.dart';
import '../services/resume_api_service.dart';
import '../widgets/employment_row.dart';
import '../widgets/photo_picker.dart';
import '../widgets/relative_row.dart';

/// Forma ekrani — veb formadagi 5 bo'limning mobil ko'rinishi:
/// 1) Shaxsiy ma'lumotlar  2) Ta'lim va faoliyat
/// 3) Mehnat faoliyati (dinamik)  4) Yaqin qarindoshlar (dinamik)
/// 5) Aloqa va hujjat ma'lumotlari
///
/// Yakuniy haqiqat manbai — backend: 422 qaytarsa xatolar maydonlar
/// bo'yicha ko'rsatiladi.
class FormScreen extends StatefulWidget {
  const FormScreen({super.key});

  @override
  State<FormScreen> createState() => _FormScreenState();
}

class _FormScreenState extends State<FormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _service = ResumeApiService();

  // ---- Asosiy maydonlar (kontrollerlar) ----
  final _fullName = TextEditingController();
  final _currentStatus = TextEditingController();
  final _currentOrganization = TextEditingController();
  final _currentPosition = TextEditingController();
  final _birthPlace = TextEditingController();
  final _nationality = TextEditingController();
  final _militaryRank = TextEditingController();
  final _institution = TextEditingController();
  final _institutionYear = TextEditingController();
  final _specialty = TextEditingController();
  final _languages = TextEditingController();
  final _awards = TextEditingController();
  final _electedBodies = TextEditingController();
  final _phone = TextEditingController();
  final _homeAddress = TextEditingController();
  final _passportInfo = TextEditingController();

  // ---- Select qiymatlari (veb standartlari bilan bir xil) ----
  String _partyAffiliation = AppConstants.partyNo;
  String _partyOther = '';
  String _education = AppConstants.educationOptions.first;
  String _academicDegree = AppConstants.partyNo;
  String _degreeOther = '';
  String _academicTitle = AppConstants.partyNo;
  String _titleOther = '';

  DateTime? _birthDate;
  File? _photo;

  // ---- Dinamik qatorlar ----
  final List<EmploymentEntry> _employment = [EmploymentEntry()];
  final List<RelativeEntry> _relatives = [RelativeEntry()];

  // ---- Validatsiya xatolari (backend maydon nomlari bilan) ----
  final Map<String, String> _errors = {};

  bool _submitting = false;

  @override
  void dispose() {
    for (final c in [
      _fullName, _currentStatus, _currentOrganization, _currentPosition,
      _birthPlace, _nationality, _militaryRank, _institution,
      _institutionYear, _specialty, _languages, _awards,
      _electedBodies, _phone, _homeAddress, _passportInfo,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  // ------------------------------------------------------------------
  //  Submit
  // ------------------------------------------------------------------
  Future<void> _submit() async {
    if (_submitting) {
      return;
    }

    setState(() => _errors.clear());

    final clientErrors = _clientSideValidate();

    if (clientErrors.isNotEmpty) {
      setState(() => _errors.addAll(clientErrors));
      _showErrorSummary(clientErrors.values.toList());
      return;
    }

    setState(() => _submitting = true);

    try {
      final result = await _service.generatePdf(
        fields: _collectFields(),
        arrayFields: _collectArrayFields(),
        photo: _photo,
      );

      if (!mounted) {
        return;
      }

      // Reklama: har 3-PDF'dan keyin interstitial (PDF olingach ko'rsatiladi)
      AdService.onPdfGenerated(context);

      // PDF avtomatik ochiladi (PDF o'quvchi ilovasi bilan)
      final openError = await ResumeApiService.openPdf(result.file.path);

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(openError.type.name == 'done'
            ? 'PDF saqlandi va ochildi ✅'
            : 'PDF saqlandi: ${result.file.path}'),
        duration: const Duration(seconds: 4),
      ));
    } on ValidationException catch (e) {
      if (!mounted) {
        return;
      }

      setState(() {
        for (final entry in e.errors.entries) {
          final msg = entry.value.isNotEmpty ? entry.value.first : null;
          if (msg != null) {
            _errors[entry.key] = msg;
          }
        }
      });
      _showErrorSummary(e.allMessages());
    } on ApiException catch (e) {
      if (mounted) {
        _showErrorSummary([e.message]);
      }
    } catch (e) {
      if (mounted) {
        _showErrorSummary([ApiClient.friendlyError(e)]);
      }
    } finally {
      if (mounted) {
        setState(() => _submitting = false);
      }
    }
  }

  /// Backend'ga yuborishdan oldin tezkor tekshiruv (majburiy maydonlar).
  Map<String, String> _clientSideValidate() {
    final errors = <String, String>{};

    String req(String value, String key, String message) {
      if (value.trim().isEmpty) {
        errors[key] = message;
      }
      return value;
    }

    req(_fullName.text, 'full_name', 'F.I.Sh. kiritilishi shart.');
    req(_birthPlace.text, 'birth_place', "Tug'ilgan joy kiritilishi shart.");
    req(_nationality.text, 'nationality', 'Millati kiritilishi shart.');
    req(_institution.text, 'institution', "Ta'lim muassasasi kiritilishi shart.");
    req(_homeAddress.text, 'home_address', 'Uy manzili kiritilishi shart.');

    if (_photo == null) {
      errors['photo'] = 'Profil rasmi yuklanishi shart.';
    }

    if (_partyAffiliation == AppConstants.partyOther &&
        _partyOther.trim().isEmpty) {
      errors['party_affiliation_other'] =
          'Partiyaviyligi "boshqa" tanlanganda izoh kiritilishi shart.';
    }

    if (_academicDegree == 'boshqa' && _degreeOther.trim().isEmpty) {
      errors['academic_degree_other'] =
          'Ilmiy darajasi "boshqa" tanlanganda izoh kiritilishi shart.';
    }

    if (_academicTitle == 'boshqa' && _titleOther.trim().isEmpty) {
      errors['academic_title_other'] =
          'Ilmiy unvon "boshqa" tanlanganda izoh kiritilishi shart.';
    }

    for (var i = 0; i < _employment.length; i++) {
      if (_employment[i].period.trim().isEmpty) {
        errors['employment.$i.period'] =
            'Mehnat faoliyati ${i + 1}-qatorida davri kiritilishi shart.';
      }
      if (_employment[i].organization.trim().isEmpty) {
        errors['employment.$i.organization'] =
            'Mehnat faoliyati ${i + 1}-qatorida tashkilot nomi kiritilishi shart.';
      }
    }

    for (var i = 0; i < _relatives.length; i++) {
      final r = _relatives[i];

      if (r.fullName.trim().isEmpty) {
        errors['relatives.$i.full_name'] =
            '${i + 1}-qarindoshning F.I.Sh. kiritilishi shart.';
      }
      if (r.isOtherRelationship && r.relationshipOther.trim().isEmpty) {
        errors['relatives.$i.relationship_other'] =
            'Qarindoshlik "Boshqa" tanlanganda izoh kiritilishi shart.';
      }
    }

    return errors;
  }

  Map<String, String> _collectFields() {
    String? when(String value) {
      final v = value.trim();
      return v.isEmpty ? null : v;
    }

    final fields = <String, String>{
      'full_name': _fullName.text.trim(),
      'birth_place': _birthPlace.text.trim(),
      'nationality': _nationality.text.trim(),
      'party_affiliation': _partyAffiliation,
      'education': _education,
      'institution': _institution.text.trim(),
      'academic_degree': _academicDegree,
      'academic_title': _academicTitle,
      'home_address': _homeAddress.text.trim(),
    };

    void putIfNotEmpty(String key, String value) {
      final v = value.trim();
      if (v.isNotEmpty) {
        fields[key] = v;
      }
    }

    putIfNotEmpty('current_status', _currentStatus.text);
    putIfNotEmpty('current_organization', _currentOrganization.text);
    putIfNotEmpty('current_position', _currentPosition.text);
    putIfNotEmpty('military_rank', _militaryRank.text);
    putIfNotEmpty('specialty', _specialty.text);
    putIfNotEmpty('institution_year', _institutionYear.text);
    putIfNotEmpty('languages', _languages.text);
    putIfNotEmpty('awards', _awards.text);
    putIfNotEmpty('elected_bodies', _electedBodies.text);
    putIfNotEmpty('phone', _phone.text);
    putIfNotEmpty('passport_info', _passportInfo.text);

    // "Boshqa" izohlari — faqat mos tanlovda yuboriladi
    if (_partyAffiliation == AppConstants.partyOther && _partyOther.trim().isNotEmpty) {
      fields['party_affiliation_other'] = _partyOther.trim();
    }
    if (_academicDegree == 'boshqa' && _degreeOther.trim().isNotEmpty) {
      fields['academic_degree_other'] = _degreeOther.trim();
    }
    if (_academicTitle == 'boshqa' && _titleOther.trim().isNotEmpty) {
      fields['academic_title_other'] = _titleOther.trim();
    }

    final birth = _birthDate;
    if (birth != null) {
      fields['birth_date'] = '${birth.year.toString().padLeft(4, '0')}-'
          '${birth.month.toString().padLeft(2, '0')}-'
          '${birth.day.toString().padLeft(2, '0')}';
    }

    return fields;
  }

  Map<String, List<String>> _collectArrayFields() {
    final arrays = <String, List<String>>{};

    for (var i = 0; i < _employment.length; i++) {
      final e = _employment[i];
      arrays['employment[$i][period]'] = [e.period.trim()];
      arrays['employment[$i][organization]'] = [e.organization.trim()];
      if (e.position.trim().isNotEmpty) {
        arrays['employment[$i][position]'] = [e.position.trim()];
      }
    }

    for (var i = 0; i < _relatives.length; i++) {
      final r = _relatives[i];

      arrays['relatives[$i][relationship]'] = [r.relationship];
      arrays['relatives[$i][full_name]'] = [r.fullName.trim()];

      void put(String key, String value) {
        if (value.trim().isNotEmpty) {
          arrays['relatives[$i][$key]'] = [value.trim()];
        }
      }

      if (r.isOtherRelationship) {
        put('relationship_other', r.relationshipOther);
      }
      put('birth_year', r.birthYear);
      put('birth_place', r.birthPlace);
      put('workplace', r.workplace);
      put('position', r.position);
      put('address', r.address);
      put('phone', r.phone);
    }

    return arrays;
  }

  void _showErrorSummary(List<String> messages) {
    if (!mounted || messages.isEmpty) {
      return;
    }

    showDialog<void>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: Colors.white,
        title: const Text('Xatoliklar'),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              for (final m in messages.take(10))
                Padding(
                  padding: const EdgeInsets.only(bottom: 6),
                  child: Text('• $m'),
                ),
              if (messages.length > 10)
                Text('... va yana ${messages.length - 10} ta xato'),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Tushunarli'),
          ),
        ],
      ),
    );
  }

  // ------------------------------------------------------------------
  //  Dinamik qatorlar
  // ------------------------------------------------------------------
  void _addEmployment() {
    if (_employment.length >= AppConstants.employmentMax) {
      return;
    }
    setState(() => _employment.add(EmploymentEntry()));
  }

  void _removeEmployment(int index) {
    // Kamida bitta qator qoladi
    if (_employment.length <= 1) {
      return;
    }
    setState(() => _employment.removeAt(index));
  }

  void _addRelative() {
    if (_relatives.length >= AppConstants.relativesMax) {
      return;
    }
    setState(() => _relatives.add(RelativeEntry()));
  }

  void _removeRelative(int index) {
    if (_relatives.length <= 1) {
      return;
    }
    setState(() => _relatives.removeAt(index));
  }

  Future<void> _pickBirthDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _birthDate ?? DateTime(now.year - 20, now.month, now.day),
      firstDate: DateTime(now.year - 100),
      lastDate: now,
    );

    if (picked != null) {
      setState(() => _birthDate = picked);
    }
  }

  // ------------------------------------------------------------------
  //  UI
  // ------------------------------------------------------------------
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      appBar: AppBar(
        backgroundColor: AppColors.ink,
        foregroundColor: AppColors.parchment,
        title: const Text("Ma'lumotnoma yaratish"),
      ),
      body: Form(
        key: _formKey,
        autovalidateMode: AutovalidateMode.disabled,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _buildPersonalSection(),
            _buildEducationSection(),
            _buildEmploymentSection(),
            _buildRelativesSection(),
            _buildContactsSection(),
            const SizedBox(height: 24),
            FilledButton(
              onPressed: _submitting ? null : _submit,
              style: FilledButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              child: _submitting
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        color: AppColors.ink,
                      ),
                    )
                  : const Text(
                      'PDF yuklab olish',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                    ),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _sectionCard({required String title, required List<Widget> children}) {
    return Card(
      color: Colors.white,
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(10),
        side: BorderSide(color: Colors.grey.shade300),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              title,
              style: const TextStyle(
                color: AppColors.ink,
                fontWeight: FontWeight.w700,
                fontSize: 15,
              ),
            ),
            const SizedBox(height: 12),
            ...children,
          ],
        ),
      ),
    );
  }

  String? _errorText(String key) => _errors[key];

  Widget _buildPersonalSection() {
    return _sectionCard(title: 'Shaxsiy ma\'lumotlar', children: [
      TextFormField(
        controller: _fullName,
        decoration: InputDecoration(
          labelText: "F.I.Sh. *",
          hintText: "Yarashev Sardor O'tabek o'g'li",
          errorText: _errorText('full_name'),
        ),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      PhotoPicker(
        photo: _photo,
        onPicked: (file) => setState(() {
          _photo = file;
          _errors.remove('photo');
        }),
        onError: (message) => setState(() => _errors['photo'] = message),
      ),
      if (_errors['photo'] != null) PhotoErrorText(message: _errors['photo']!),
      const SizedBox(height: 12),
      TextFormField(
        controller: _currentStatus,
        decoration: const InputDecoration(
          labelText: "Hozirgi holati (ishlayapti / o'qiyapti)",
        ),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _currentOrganization,
        decoration: const InputDecoration(labelText: 'Tashkilot / OTM nomi'),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _currentPosition,
        decoration: const InputDecoration(
          labelText: "Fakultet, yo'nalish yoki lavozim",
        ),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      // Tug'ilgan sana — date picker
      InkWell(
        onTap: _pickBirthDate,
        borderRadius: BorderRadius.circular(8),
        child: InputDecorator(
          decoration: InputDecoration(
            labelText: "Tug'ilgan sanasi",
            suffixIcon: const Icon(Icons.calendar_today_outlined, size: 20),
            errorText: _errorText('birth_date'),
          ),
          child: Text(
            _birthDate == null
                ? ''
                : '${_birthDate!.day.toString().padLeft(2, '0')}.'
                    '${_birthDate!.month.toString().padLeft(2, '0')}.'
                    '${_birthDate!.year}',
            style: TextStyle(
              color: _birthDate == null ? Colors.grey.shade500 : AppColors.ink,
            ),
          ),
        ),
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _birthPlace,
        decoration: InputDecoration(
          labelText: "Tug'ilgan joyi *",
          hintText: 'Samarqand viloyati',
          errorText: _errorText('birth_place'),
        ),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _nationality,
        decoration: InputDecoration(
          labelText: 'Millati *',
          hintText: "o'zbek",
          errorText: _errorText('nationality'),
        ),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      DropdownButtonFormField<String>(
        initialValue: _partyAffiliation,
        decoration: InputDecoration(
          labelText: 'Partiyaviyligi *',
          errorText: _errorText('party_affiliation'),
        ),
        items: AppConstants.partyAffiliationOptions
            .map((v) => DropdownMenuItem(value: v, child: Text(v)))
            .toList(),
        onChanged: (v) => setState(() => _partyAffiliation = v ?? _partyAffiliation),
      ),
      if (_partyAffiliation == AppConstants.partyOther) ...[
        const SizedBox(height: 12),
        TextFormField(
          decoration: InputDecoration(
            labelText: 'Partiyaviyligi — izoh *',
            hintText: 'Partiya nomi',
            errorText: _errorText('party_affiliation_other'),
          ),
          onChanged: (v) => _partyOther = v,
        ),
      ],
      const SizedBox(height: 12),
      TextFormField(
        controller: _militaryRank,
        decoration: const InputDecoration(labelText: 'Harbiy unvoni'),
        textInputAction: TextInputAction.next,
      ),
    ]);
  }

  Widget _buildEducationSection() {
    return _sectionCard(title: "Ta'lim va faoliyat", children: [
      DropdownButtonFormField<String>(
        initialValue: _education,
        decoration: InputDecoration(
          labelText: "Ma'lumoti *",
          errorText: _errorText('education'),
        ),
        items: AppConstants.educationOptions
            .map((v) => DropdownMenuItem(value: v, child: Text(v)))
            .toList(),
        onChanged: (v) => setState(() => _education = v ?? _education),
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _institution,
        decoration: InputDecoration(
          labelText: "Tamomlagan ta'lim muassasasi *",
          hintText: 'Samarqand davlat universiteti',
          errorText: _errorText('institution'),
        ),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _institutionYear,
        decoration: const InputDecoration(labelText: "Tamomlagan yili"),
        keyboardType: TextInputType.number,
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _specialty,
        decoration: const InputDecoration(
          labelText: "Ma'lumoti bo'yicha mutaxassisligi",
          hintText: 'Dasturiy injiniring',
        ),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      DropdownButtonFormField<String>(
        initialValue: _academicDegree,
        decoration: InputDecoration(
          labelText: 'Ilmiy darajasi *',
          errorText: _errorText('academic_degree'),
        ),
        items: AppConstants.academicDegreeOptions
            .map((v) => DropdownMenuItem(value: v, child: Text(v)))
            .toList(),
        onChanged: (v) => setState(() => _academicDegree = v ?? _academicDegree),
      ),
      if (_academicDegree == 'boshqa') ...[
        const SizedBox(height: 12),
        TextFormField(
          decoration: InputDecoration(
            labelText: 'Ilmiy darajasi — izoh *',
            errorText: _errorText('academic_degree_other'),
          ),
          onChanged: (v) => _degreeOther = v,
        ),
      ],
      const SizedBox(height: 12),
      DropdownButtonFormField<String>(
        initialValue: _academicTitle,
        decoration: InputDecoration(
          labelText: 'Ilmiy unvoni *',
          errorText: _errorText('academic_title'),
        ),
        items: AppConstants.academicTitleOptions
            .map((v) => DropdownMenuItem(value: v, child: Text(v)))
            .toList(),
        onChanged: (v) => setState(() => _academicTitle = v ?? _academicTitle),
      ),
      if (_academicTitle == 'boshqa') ...[
        const SizedBox(height: 12),
        TextFormField(
          decoration: InputDecoration(
            labelText: 'Ilmiy unvoni — izoh *',
            errorText: _errorText('academic_title_other'),
          ),
          onChanged: (v) => _titleOther = v,
        ),
      ],
    ]);
  }

  Widget _buildEmploymentSection() {
    final canAdd = _employment.length < AppConstants.employmentMax;

    return _sectionCard(title: 'Mehnat faoliyati', children: [
      for (var i = 0; i < _employment.length; i++)
        EmploymentRow(
          entry: _employment[i],
          index: i,
          canRemove: _employment.length > 1,
          onRemove: () => _removeEmployment(i),
        ),
      OutlinedButton.icon(
        onPressed: canAdd ? _addEmployment : null,
        icon: const Icon(Icons.add),
        label: const Text('Qator qo\'shish'),
      ),
    ]);
  }

  Widget _buildRelativesSection() {
    final canAdd = _relatives.length < AppConstants.relativesMax;

    return _sectionCard(title: 'Yaqin qarindoshlar', children: [
      for (var i = 0; i < _relatives.length; i++)
        RelativeRow(
          entry: _relatives[i],
          index: i,
          canRemove: _relatives.length > 1,
          onRemove: () => _removeRelative(i),
          onChanged: () => setState(() {}),
        ),
      OutlinedButton.icon(
        onPressed: canAdd ? _addRelative : null,
        icon: const Icon(Icons.add),
        label: const Text("Qarindosh qo'shish"),
      ),
    ]);
  }

  Widget _buildContactsSection() {
    return _sectionCard(title: "Aloqa va hujjat ma'lumotlari", children: [
      TextFormField(
        controller: _languages,
        decoration: const InputDecoration(
          labelText: 'Chet tillari',
          hintText: 'inglizcha, ruscha',
        ),
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _awards,
        decoration: const InputDecoration(labelText: 'Davlat mukofotlari'),
        maxLines: 2,
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _electedBodies,
        decoration: const InputDecoration(
          labelText: "Deputatlik ma'lumoti",
        ),
        maxLines: 2,
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _phone,
        decoration: const InputDecoration(
          labelText: 'Mobil raqam',
          hintText: '+998 90 123 45 67',
        ),
        keyboardType: TextInputType.phone,
        textInputAction: TextInputAction.next,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _homeAddress,
        decoration: InputDecoration(
          labelText: 'Uy manzili *',
          hintText: "Samarqand sh., Registon ko'chasi 1",
          errorText: _errorText('home_address'),
        ),
        maxLines: 2,
      ),
      const SizedBox(height: 12),
      TextFormField(
        controller: _passportInfo,
        decoration: const InputDecoration(
          labelText: 'Pasport ma\'lumotlari',
          hintText: 'AA1234567',
        ),
        textInputAction: TextInputAction.done,
      ),
    ]);
  }
}
