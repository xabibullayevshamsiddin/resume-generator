@extends('layouts.home')

@section('title', "Ma'lumotnoma generatori")

@section('content')
    <section class="hero">
        <div class="hero__seal" aria-hidden="true">
            <svg viewBox="0 0 200 200" focusable="false" aria-hidden="true">
                <circle cx="100" cy="100" r="96" />
                <circle cx="100" cy="100" r="78" />
                <circle cx="100" cy="100" r="34" />
                <path d="M100 8 v14 M100 178 v14 M8 100 h14 M178 100 h14
                         M35 35 l10 10 M155 155 l10 10 M165 35 l-10 10 M45 155 l-10 10" />
            </svg>
        </div>

        <h1 class="hero__title">Ma&rsquo;lumotnoma <em>generatori</em></h1>

        <p class="hero__subtitle">
            Ma&rsquo;lumotlaringizni kiriting va tayyor rasmiy hujjatni oling
        </p>

        <div class="hero__actions">
            <a href="{{ resume_route('resume.form') }}" class="hero__btn hero__btn--primary">
                Ma&rsquo;lumotnoma yaratish
            </a>

            <button type="button" class="hero__btn hero__btn--secondary"
                    @unless(file_exists(public_path('videos/qollanma.mp4'))) disabled title="Video tez orada joylanadi" @endunless
                    data-video-trigger>
                Video qo&rsquo;llanmani ko&rsquo;rish
            </button>
        </div>
    </section>

    @if(file_exists(public_path('videos/qollanma.mp4')))
        <dialog id="video-modal" class="video-modal" aria-label="Video qo'llanma">
            <button type="button" class="video-modal__close" data-video-close aria-label="Yopish">&#10005;</button>
            <video controls preload="none" class="video-modal__player">
                <source src="{{ asset('videos/qollanma.mp4') }}" type="video/mp4">
            </video>
        </dialog>
    @endif
@endsection
