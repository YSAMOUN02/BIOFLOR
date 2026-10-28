@extends('layouts.app')

@section('title', __('All news').' | '.brand())
@section('meta_description', $page ? str_replace('{brand}', brand(), (string) $page->meta_description) : '')

@section('content')
    <div class="container breadcrumb-row">
        @include('partials.breadcrumb', ['items' => [[__('Our news'), route('page', ['slug' => 'our-news'])], [__('All news'), null]]])
    </div>

    <div class="topic-links">
        <div class="container">
            <p class="topic-links__label card-title">{{ __('Browse by topic') }}</p>
            <ul class="topic-links__list">
                <li><a class="chip {{ ! $tag ? 'is-active' : '' }}" href="{{ route('articles', array_filter(['sort' => $sort === 'oldest' ? 'oldest' : null])) }}" @if (! $tag) aria-current="page" @endif>{{ __('All') }}</a></li>
                @foreach ($tags as $topic)
                    <li><a class="chip {{ $tag === $topic ? 'is-active' : '' }}" href="{{ route('articles', array_filter(['tag' => $topic, 'sort' => $sort === 'oldest' ? 'oldest' : null])) }}" @if ($tag === $topic) aria-current="page" @endif>{{ __($topic) }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>

    <section class="container news-overview">
        <div class="news-overview__head">
            <div>
                <h1 class="h1-sm">{{ $tag ? __($tag) : __('All news') }}</h1>
                <p class="text-intro">{{ trans_choice(':count article|:count articles', $total, ['count' => $total]) }}</p>
            </div>
            <form class="news-overview__sort js-autosubmit" method="get" action="{{ route('articles') }}">
                @if ($tag)<input type="hidden" name="tag" value="{{ $tag }}">@endif
                <label class="field__label" for="news-sort">{{ __('Sort by') }}</label>
                <select class="field__input field__select" id="news-sort" name="sort">
                    <option value="newest" @selected($sort === 'newest')>{{ __('Newest first') }}</option>
                    <option value="oldest" @selected($sort === 'oldest')>{{ __('Oldest first') }}</option>
                </select>
                <noscript><button class="btn btn--navy btn--sm" type="submit">{{ __('Apply') }}</button></noscript>
            </form>
        </div>

        @if ($articles->isEmpty())
            <p class="empty-state">{{ __('No articles in this topic yet.') }}</p>
        @else
            <div class="article-grid">
                @foreach ($articles as $article)
                    @include('partials.article-tile', ['article' => $article, 'uid' => 'list'])
                @endforeach
            </div>

            @if ($total > $articles->count())
                <div class="news-overview__more">
                    <a class="btn btn--navy" href="{{ route('articles', array_filter(['tag' => $tag, 'sort' => $sort === 'oldest' ? 'oldest' : null, 'show' => $show + $perPage])) }}#article-{{ $articles->last()->id }}-list">
                        {{ __('Show more articles') }}
                    </a>
                </div>
            @endif
        @endif
    </section>
@endsection
