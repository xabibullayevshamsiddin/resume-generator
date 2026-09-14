@extends('layouts.app')

@section('title', "Saqlangan ma'lumotnomalar")

@section('content')
    <header class="form-header">
        <h1 class="form-header__title">Saqlangan ma'lumotnomalar</h1>
        <p class="form-header__subtitle">
            <a href="{{ resume_route('resume.form') }}">← Yangi ma'lumotnoma yaratish</a>
        </p>
    </header>

    @if(session('success'))
        <div class="alert alert--success" role="status">{{ session('success') }}</div>
    @endif

    <div class="card">
        @if($resumes->count())
            <table class="rows-table rows-table--index">
                <thead>
                    <tr>
                        <th>F.I.Sh.</th>
                        <th>Tashkilot</th>
                        <th>Sana</th>
                        <th>Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumes as $resume)
                        <tr>
                            <td data-label="F.I.Sh.">{{ $resume->full_name }}</td>
                            <td data-label="Tashkilot">{{ $resume->current_organization ?: '—' }}</td>
                            <td data-label="Sana">{{ $resume->created_at->format('d.m.Y H:i') }}</td>
                            <td class="cell-index-actions" data-label="Amallar">
                                <div class="index-actions">
                                    <a class="btn btn--secondary" href="{{ resume_route('resume.show', $resume->getKey()) }}">Ko'rish</a>
                                    <a class="btn btn--primary" href="{{ resume_route('resume.regenerate', $resume->getKey()) }}">PDF</a>
                                    <form action="{{ resume_route('resume.destroy', $resume->getKey()) }}" method="POST" class="inline-form"
                                          onsubmit="return confirm('O'chirilsinmi?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn--danger-outline">O'chirish</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @include('resume.partials.pagination', ['paginator' => $resumes])
        @else
            <p class="empty-state">Hozircha saqlangan ma'lumotnoma yo'q.</p>
        @endif
    </div>
@endsection
