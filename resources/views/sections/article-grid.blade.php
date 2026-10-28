@php $articles = $section->articles(); @endphp
@if ($articles->isNotEmpty())
    <section class="container article-grid-section">
        @if ($section->get('title'))
            <h2 class="article-grid-section__title">{!! $section->get('title') !!}</h2>
        @endif
        @if ($section->get('intro'))
            <p class="text-intro article-grid-section__intro">{!! $section->get('intro') !!}</p>
        @endif
        <div class="article-grid">
            @foreach ($articles as $article)
                @include('partials.article-tile', ['article' => $article, 'uid' => $section->uid()])
            @endforeach
        </div>
    </section>
@endif
