@extends('layouts.app')

@section('title', $resume->full_name." — Ma'lumotnoma")

@section('content')
    <header class="form-header">
        <h1 class="form-header__title">{{ $resume->full_name }}</h1>
        <p class="form-header__subtitle">
            <a href="{{ resume_route('resume.index') }}">← Ro'yxatga qaytish</a>
        </p>
    </header>

    <div class="card">
        <h2 class="card__title">Ma'lumotnoma ma'lumotlari</h2>

        <table class="doc__personal-table show-table">
            <tr>
                <td class="doc__label">Tug'ilgan sanasi:</td>
                <td class="doc__value">{{ $resume->birth_date?->format('d.m.Y') ?? '—' }}</td>
                <td class="doc__label">Millati:</td>
                <td class="doc__value">{{ $resume->nationality }}</td>
            </tr>
            <tr>
                <td class="doc__label">Tug'ilgan joyi:</td>
                <td class="doc__value">{{ $resume->birth_place }}</td>
                <td class="doc__label">Partiyaviyligi:</td>
                <td class="doc__value">{{ $resume->party_affiliation }}</td>
            </tr>
            <tr>
                <td class="doc__label">Ma'lumoti:</td>
                <td class="doc__value">{{ $resume->education }}</td>
                <td class="doc__label">Muassasa:</td>
                <td class="doc__value">{{ $resume->institution }}</td>
            </tr>
            <tr>
                <td class="doc__label">Ilmiy darajasi:</td>
                <td class="doc__value">{{ $resume->academic_degree }}</td>
                <td class="doc__label">Ilmiy unvoni:</td>
                <td class="doc__value">{{ $resume->academic_title }}</td>
            </tr>
            <tr>
                <td class="doc__label">Telefon:</td>
                <td class="doc__value">{{ $resume->phone ?? '—' }}</td>
                <td class="doc__label">Pasport:</td>
                <td class="doc__value">{{ $resume->passport_info ? "ko'rsatilgan" : '—' }}</td>
            </tr>
        </table>

        <div class="rows-actions">
            <a class="btn btn--primary" href="{{ resume_route('resume.regenerate', $resume->getKey()) }}">PDF yuklab olish</a>
            <a class="btn btn--secondary" href="{{ resume_route('resume.regenerate', $resume->getKey(), ['format' => 'docx']) }}">Word (.docx)</a>
            <a class="btn btn--secondary" href="{{ resume_route('resume.index') }}">Ro'yxat</a>
        </div>
    </div>
@endsection
