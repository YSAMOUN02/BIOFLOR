{{-- Topic chips linking to the filtered "all news" listing. --}}
<div class="topic-links">
    <div class="container">
        @if ($section->get('label'))
            <p class="topic-links__label card-title">{{ $section->get('label') }}</p>
        @endif
        <ul class="topic-links__list">
            @foreach ((array) $section->get('topics', []) as $topic)
                <li><a class="chip" href="{{ route('articles', ['tag' => $topic]) }}">{{ __($topic) }}</a></li>
            @endforeach
            <li><a class="chip chip--strong" href="{{ route('articles') }}">{{ __('All news') }}</a></li>
        </ul>
    </div>
</div>
