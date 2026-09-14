@if($paginator->lastPage() > 1)
    <nav class="pagination" aria-label="Sahifalar">
        {{-- Oldingi sahifa --}}
        @if($paginator->onFirstPage())
            <span class="page-link page-link--disabled" aria-disabled="true">←</span>
        @else
            <a class="page-link" href="{{ resume_action_url('/resumes', ['page' => $paginator->currentPage() - 1]) }}" rel="prev">←</a>
        @endif

        {{-- Raqamlar --}}
        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();
            $start = max(1, $current - 2);
            $end = min($last, $current + 2);

            if ($start > 1) {
                echo '<span class="page-link page-link--disabled">…</span>';
            }
        @endphp

        @for($page = $start; $page <= $end; $page++)
            @if($page === $current)
                <span class="page-link page-link--active" aria-current="page">{{ $page }}</span>
            @else
                <a class="page-link" href="{{ resume_action_url('/resumes', ['page' => $page]) }}">{{ $page }}</a>
            @endif
        @endfor

        @if($end < $last)
            <span class="page-link page-link--disabled">…</span>
        @endif

        {{-- Keyingi sahifa --}}
        @if($paginator->hasMorePages())
            <a class="page-link" href="{{ resume_action_url('/resumes', ['page' => $paginator->currentPage() + 1]) }}" rel="next">→</a>
        @else
            <span class="page-link page-link--disabled" aria-disabled="true">→</span>
        @endif
    </nav>
@endif
