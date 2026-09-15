@extends('layouts.app')

@section('title', "Ma'lumotnoma generatori")

@section('content')
    <header class="form-header">
        <h1 class="form-header__title">Ma'lumotnoma generatori</h1>
        <p class="form-header__subtitle">
            Ma'lumotlaringizni kiriting va tayyor PDF hujjatni yuklab oling
            · <a href="{{ resume_route('resume.index') }}">Saqlanganlar</a>
        </p>
    </header>

    @if($errors->any())
        <div class="alert alert--error" role="alert">
            <strong>Formada xatolar topildi:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="js-resume-form"
        action="{{ resume_pdf_action() }}"
        method="POST"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf

        {{-- ===== 1. Shaxsiy ma'lumotlar ===== --}}
        <section class="card" aria-labelledby="section-personal">
            <h2 class="card__title" id="section-personal">Shaxsiy ma'lumotlar</h2>

            <div class="grid">
                <div class="field field--span-2">
                    <label class="field__label" for="full_name">To'liq F.I.Sh. <span class="required" aria-hidden="true">*</span></label>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="{{ old('full_name') }}"
                        placeholder="Yarashev Sardor O'tabek o'g'li"
                        class="input @error('full_name') input--error @enderror"
                        required
                        aria-required="true"
                        @error('full_name') aria-invalid="true" aria-describedby="full_name-error" @enderror
                    >
                    @error('full_name')
                        <p class="field__error" id="full_name-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field field--photo">
                    <label class="field__label" for="photo">Profil rasmi (3x4) <span class="required" aria-hidden="true">*</span></label>
                    <input
                        type="file"
                        id="photo"
                        name="photo"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="input input--file @error('photo') input--error @enderror"
                        required
                        aria-required="true"
                        @error('photo') aria-invalid="true" aria-describedby="photo-error" @enderror
                        data-max-size="3145728"
                    >
                    <div class="photo-preview is-hidden" id="js-photo-preview">
                        <img src="" alt="Tanlangan rasm" id="js-photo-preview-img">
                    </div>
                    @error('photo')
                        <p class="field__error" id="photo-error">{{ $message }}</p>
                    @enderror
                    @if($errors->any())
                        <p class="field__hint">Yuklangan rasm xavfsizlik sababli saqlanmaydi — iltimos, qayta tanlang.</p>
                    @endif
                </div>

                <div class="field">
                    <label class="field__label" for="current_status">Hozirgi holat boshlangan sana yoki matn</label>
                    <input
                        type="text"
                        id="current_status"
                        name="current_status"
                        value="{{ old('current_status') }}"
                        placeholder="2026-yil 7-sentyabrdan"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="current_organization">Hozirgi tashkilot/universitet nomi</label>
                    <input
                        type="text"
                        id="current_organization"
                        name="current_organization"
                        value="{{ old('current_organization') }}"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="current_position">Fakultet, yo'nalish yoki lavozim</label>
                    <input
                        type="text"
                        id="current_position"
                        name="current_position"
                        value="{{ old('current_position') }}"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="birth_date">Tug'ilgan sana</label>
                    <input
                        type="date"
                        id="birth_date"
                        name="birth_date"
                        value="{{ old('birth_date') }}"
                        max="{{ now()->toDateString() }}"
                        class="input @error('birth_date') input--error @enderror"
                        @error('birth_date') aria-invalid="true" aria-describedby="birth_date-error" @enderror
                    >
                    @error('birth_date')
                        <p class="field__error" id="birth_date-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="birth_place">Tug'ilgan joyi <span class="required" aria-hidden="true">*</span></label>
                    <input
                        type="text"
                        id="birth_place"
                        name="birth_place"
                        value="{{ old('birth_place') }}"
                        placeholder="Samarqand viloyati, Kattaqo'rg'on tumani"
                        class="input @error('birth_place') input--error @enderror"
                        required
                        aria-required="true"
                        @error('birth_place') aria-invalid="true" aria-describedby="birth_place-error" @enderror
                    >
                    @error('birth_place')
                        <p class="field__error" id="birth_place-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="nationality">Millati <span class="required" aria-hidden="true">*</span></label>
                    <input
                        type="text"
                        id="nationality"
                        name="nationality"
                        value="{{ old('nationality') }}"
                        placeholder="o'zbek"
                        class="input @error('nationality') input--error @enderror"
                        required
                        aria-required="true"
                        @error('nationality') aria-invalid="true" aria-describedby="nationality-error" @enderror
                    >
                    @error('nationality')
                        <p class="field__error" id="nationality-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="party_affiliation">Partiyaviyligi <span class="required" aria-hidden="true">*</span></label>
                    <select
                        id="party_affiliation"
                        name="party_affiliation"
                        class="input select @error('party_affiliation') input--error @enderror"
                        data-other-target="party_affiliation_other-field"
                        required
                        aria-required="true"
                        @error('party_affiliation') aria-invalid="true" aria-describedby="party_affiliation-error" @enderror
                    >
                        <option value="yo'q" @selected(old('party_affiliation', 'yo\'q') === 'yo\'q')>yo'q</option>
                        <option value="boshqa" @selected(old('party_affiliation') === 'boshqa')>boshqa</option>
                    </select>
                    @error('party_affiliation')
                        <p class="field__error" id="party_affiliation-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field is-hidden" id="party_affiliation_other-field">
                    <label class="field__label" for="party_affiliation_other">Partiyaviyligi — izoh</label>
                    <input
                        type="text"
                        id="party_affiliation_other"
                        name="party_affiliation_other"
                        value="{{ old('party_affiliation_other') }}"
                        placeholder="Partiya nomi"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="military_rank">Harbiy unvoni</label>
                    <input
                        type="text"
                        id="military_rank"
                        name="military_rank"
                        value="{{ old('military_rank') }}"
                        class="input"
                    >
                </div>
            </div>
        </section>

        {{-- ===== 2. Ta'lim va faoliyat ===== --}}
        <section class="card" aria-labelledby="section-education">
            <h2 class="card__title" id="section-education">Ta'lim va faoliyat</h2>

            <div class="grid">
                <div class="field">
                    <label class="field__label" for="education">Ma'lumoti <span class="required" aria-hidden="true">*</span></label>
                    <select
                        id="education"
                        name="education"
                        class="input select @error('education') input--error @enderror"
                        required
                        aria-required="true"
                        @error('education') aria-invalid="true" aria-describedby="education-error" @enderror
                    >
                        @foreach(['o\'rta', 'o\'rta maxsus', 'oliy', 'tugallanmagan oliy'] as $option)
                            <option value="{{ $option }}" @selected(old('education') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('education')
                        <p class="field__error" id="education-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="institution">Tamomlagan ta'lim muassasasi <span class="required" aria-hidden="true">*</span></label>
                    <input
                        type="text"
                        id="institution"
                        name="institution"
                        value="{{ old('institution') }}"
                        placeholder="Samarqand davlat universiteti"
                        class="input @error('institution') input--error @enderror"
                        required
                        aria-required="true"
                        @error('institution') aria-invalid="true" aria-describedby="institution-error" @enderror
                    >
                    @error('institution')
                        <p class="field__error" id="institution-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="institution_year">Tamomlagan yili</label>
                    <input
                        type="text"
                        id="institution_year"
                        name="institution_year"
                        value="{{ old('institution_year') }}"
                        placeholder="2024"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="specialty">Ma'lumoti bo'yicha mutaxassisligi</label>
                    <input
                        type="text"
                        id="specialty"
                        name="specialty"
                        value="{{ old('specialty') }}"
                        placeholder="Dasturiy injiniring"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="academic_degree">Ilmiy darajasi <span class="required" aria-hidden="true">*</span></label>
                    <select
                        id="academic_degree"
                        name="academic_degree"
                        class="input select @error('academic_degree') input--error @enderror"
                        data-other-target="academic_degree_other-field"
                        required
                        aria-required="true"
                        @error('academic_degree') aria-invalid="true" aria-describedby="academic_degree-error" @enderror
                    >
                        @foreach(['yo\'q', 'bakalavr', 'magistr', 'PhD', 'DSc', 'boshqa'] as $option)
                            <option value="{{ $option }}" @selected(old('academic_degree', 'yo\'q') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('academic_degree')
                        <p class="field__error" id="academic_degree-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field is-hidden" id="academic_degree_other-field">
                    <label class="field__label" for="academic_degree_other">Ilmiy darajasi — izoh</label>
                    <input
                        type="text"
                        id="academic_degree_other"
                        name="academic_degree_other"
                        value="{{ old('academic_degree_other') }}"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="academic_title">Ilmiy unvoni <span class="required" aria-hidden="true">*</span></label>
                    <select
                        id="academic_title"
                        name="academic_title"
                        class="input select @error('academic_title') input--error @enderror"
                        data-other-target="academic_title_other-field"
                        required
                        aria-required="true"
                        @error('academic_title') aria-invalid="true" aria-describedby="academic_title-error" @enderror
                    >
                        @foreach(['yo\'q', 'dotsent', 'professor', 'boshqa'] as $option)
                            <option value="{{ $option }}" @selected(old('academic_title', 'yo\'q') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('academic_title')
                        <p class="field__error" id="academic_title-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field is-hidden" id="academic_title_other-field">
                    <label class="field__label" for="academic_title_other">Ilmiy unvoni — izoh</label>
                    <input
                        type="text"
                        id="academic_title_other"
                        name="academic_title_other"
                        value="{{ old('academic_title_other') }}"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="languages">Biladigan chet tillari</label>
                    <input
                        type="text"
                        id="languages"
                        name="languages"
                        value="{{ old('languages') }}"
                        placeholder="inglizcha, ruscha"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="awards">Davlat mukofotlari</label>
                    <input
                        type="text"
                        id="awards"
                        name="awards"
                        value="{{ old('awards') }}"
                        class="input"
                    >
                </div>

                <div class="field field--span-2">
                    <label class="field__label" for="elected_bodies">Deputatligi yoki boshqa saylanadigan organ a'zoligi</label>
                    <textarea
                        id="elected_bodies"
                        name="elected_bodies"
                        rows="2"
                        class="input input--textarea"
                    >{{ old('elected_bodies') }}</textarea>
                </div>
            </div>
        </section>

        {{-- ===== 3. Mehnat faoliyati ===== --}}
        <section class="card" aria-labelledby="section-employment">
            <h2 class="card__title" id="section-employment">Mehnat faoliyati</h2>

            <div class="table-scroll">
                <table class="rows-table rows-table--employment">
                    <thead>
                        <tr>
                            <th style="width: 26%;">Davri</th>
                            <th style="width: 38%;">Tashkilot nomi</th>
                            <th style="width: 28%;">Lavozim / yo'nalish</th>
                            <th style="width: 8%;"></th>
                        </tr>
                    </thead>
                    <tbody id="js-employment-tbody">
                        @php($employmentOld = old('employment'))
                        @if(is_array($employmentOld) && count($employmentOld) > 0)
                            @foreach($employmentOld as $i => $row)
                                @include('resume.partials.employment-row', ['rowIndex' => $i])
                            @endforeach
                        @else
                            @include('resume.partials.employment-row', ['rowIndex' => 0])
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="rows-actions">
                <button type="button" class="btn btn--secondary" id="js-fill-first-employment">
                    Birinchi qatorni yuqoridagi joriy ma'lumotlar bilan to'ldirish
                </button>
                <button type="button" class="btn btn--secondary" id="js-add-employment">+ Qator qo'shish</button>
            </div>

            @error('employment.*.period')
                <p class="field__error">Mehnat faoliyati: har bir qatorda davr kiritilishi shart.</p>
            @enderror
            @error('employment.*.organization')
                <p class="field__error">Mehnat faoliyati: har bir qatorda tashkilot nomi kiritilishi shart.</p>
            @enderror
        </section>

        {{-- ===== 4. Yaqin qarindoshlar ===== --}}
        <section class="card" aria-labelledby="section-relatives">
            <h2 class="card__title" id="section-relatives">Yaqin qarindoshlar</h2>

            <div class="table-scroll">
                <table class="rows-table rows-table--relatives">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Qarindoshligi</th>
                            <th style="width: 16%;">F.I.Sh.</th>
                            <th style="width: 8%;">Tug'ilgan yili</th>
                            <th style="width: 14%;">Tug'ilgan joyi</th>
                            <th style="width: 14%;">Ish joyi</th>
                            <th style="width: 12%;">Lavozimi</th>
                            <th style="width: 14%;">Turar joyi</th>
                            <th style="width: 10%;">Telefon</th>
                            <th style="width: 6%;"></th>
                        </tr>
                    </thead>
                    <tbody id="js-relatives-tbody">
                        @php($relativesOld = old('relatives'))
                        @if(is_array($relativesOld) && count($relativesOld) > 0)
                            @foreach($relativesOld as $i => $row)
                                @include('resume.partials.relative-row', ['rowIndex' => $i])
                            @endforeach
                        @else
                            @include('resume.partials.relative-row', ['rowIndex' => 0])
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="rows-actions">
                <button type="button" class="btn btn--secondary" id="js-add-relative">+ Qarindosh qo'shish</button>
            </div>

            @error('relatives.*.relationship')
                <p class="field__error">Qarindoshlar: qarindoshlik darajasi tanlanishi shart.</p>
            @enderror
            @error('relatives.*.full_name')
                <p class="field__error">Qarindoshlar: har bir qatorda F.I.Sh. kiritilishi shart.</p>
            @enderror
        </section>

        {{-- ===== 5. Aloqa va hujjat ma'lumotlari ===== --}}
        <section class="card" aria-labelledby="section-contact">
            <h2 class="card__title" id="section-contact">Aloqa va hujjat ma'lumotlari</h2>

            <div class="grid">
                <div class="field">
                    <label class="field__label" for="phone">Mobil raqami</label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="+998 90 123 45 67"
                        class="input"
                    >
                </div>

                <div class="field">
                    <label class="field__label" for="home_address">Uy manzili <span class="required" aria-hidden="true">*</span></label>
                    <input
                        type="text"
                        id="home_address"
                        name="home_address"
                        value="{{ old('home_address') }}"
                        placeholder="Samarqand viloyati, ... "
                        class="input @error('home_address') input--error @enderror"
                        required
                        aria-required="true"
                        @error('home_address') aria-invalid="true" aria-describedby="home_address-error" @enderror
                    >
                    @error('home_address')
                        <p class="field__error" id="home_address-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field field--span-2">
                    <label class="field__label" for="passport_info">Pasport/ID ma'lumotlari</label>
                    <input
                        type="text"
                        id="passport_info"
                        name="passport_info"
                        value="{{ old('passport_info') }}"
                        placeholder="AA1234567, 12.05.2020-yilda berilgan"
                        class="input"
                        maxlength="255"
                    >
                </div>
            </div>
        </section>

        {{-- Har bir yuborilish bazaga saqlanadi (save_record doim 1) --}}
        <input type="hidden" name="save_record" value="1">

        <div class="form-actions">
            <button type="button" class="btn btn--secondary" id="js-preview-btn">Oldindan ko'rish</button>
            <button type="submit" class="btn btn--primary" id="js-submit-btn" name="format" value="pdf">PDF yuklab olish</button>
            <button type="submit" class="btn btn--primary" id="js-submit-docx" name="format" value="docx">Word (.docx) yuklab olish</button>
            <button type="button" class="btn btn--danger-outline" id="js-reset-btn">Formani tozalash</button>
        </div>

        <p class="privacy-note">
            Ma'lumotnoma bazaga saqlanadi — ro'yxatdan qayta yuklab olish yoki o'chirish mumkin.
        </p>
    </form>

    {{-- ===== JS preview (serverga so'rov yuborilmaydi) ===== --}}
    <div id="js-preview-root" class="preview-root is-hidden" hidden>
        <div class="preview-root__bar">
            <h2 class="preview-root__title">Oldindan ko'rish (A4)</h2>
            <button type="button" class="btn btn--secondary" id="js-preview-close">Yopish</button>
        </div>
        <div id="js-preview-pages"></div>
    </div>

    {{-- Dinamik qatorlar uchun shablonlar --}}
    <script type="text/template" id="js-employment-row-template">
        @include('resume.partials.employment-row', ['rowIndex' => '__INDEX__'])
    </script>

    <script type="text/template" id="js-relative-row-template">
        @include('resume.partials.relative-row', ['rowIndex' => '__INDEX__'])
    </script>
@endsection
