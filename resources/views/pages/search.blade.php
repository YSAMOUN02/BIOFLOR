@extends('layouts.app')

@section('title', ($q !== '' ? __('Search results for “:q”', ['q' => $q]) : __('Search')).' | '.brand())
@section('hide_newsletter', '1')
@section('robots', 'noindex, follow')

@section('content')
    <section class="search-page">
        <div class="container">
            <h1 class="h1-sm">{{ __('Search') }}</h1>
            <form class="search-form search-form--page" role="search" action="{{ route('search') }}" method="get">
                <label class="visually-hidden" for="search-page-field">{{ __('Search') }}</label>
                <input class="search-form__input" type="search" id="search-page-field" name="q" value="{{ $q }}"
                       placeholder="{{ setting('header.search_placeholder', __('Search (keywords, etc.)')) }}">
                <button class="icon-btn search-form__submit" type="submit" aria-label="{{ __('Submit search') }}">
                    @include('partials.icon', ['name' => 'search', 'size' => 22])
                </button>
            </form>

            @if ($q !== '')
                <p class="search-page__count text-intro" role="status">
                    {{ trans_choice(':count result for “:q”|:count results for “:q”', $products->count() + $articles->count(), ['count' => $products->count() + $articles->count(), 'q' => $q]) }}
                </p>
            @endif
        </div>

        @if ($q !== '' && ($products->isNotEmpty() || $articles->isNotEmpty()))
            <div class="container search-results js-tabs">
                <div class="pill-tabs" role="tablist" aria-label="{{ __('Result types') }}">
                    <button class="pill-tabs__btn" type="button" role="tab" id="results-tab-products" aria-controls="results-products"
                            aria-selected="{{ $products->isNotEmpty() ? 'true' : 'false' }}" tabindex="{{ $products->isNotEmpty() ? '0' : '-1' }}">
                        {{ __('Products') }} ({{ $products->count() }})
                    </button>
                    <button class="pill-tabs__btn" type="button" role="tab" id="results-tab-articles" aria-controls="results-articles"
                            aria-selected="{{ $products->isEmpty() ? 'true' : 'false' }}" tabindex="{{ $products->isEmpty() ? '0' : '-1' }}">
                        {{ __('Articles') }} ({{ $articles->count() }})
                    </button>
                </div>

                <div class="pill-tabs__panel" role="tabpanel" id="results-products" aria-labelledby="results-tab-products" tabindex="0" @if ($products->isEmpty()) hidden @endif>
                    @if ($products->isEmpty())
                        <p class="empty-state">{{ __('No products match your search.') }}</p>
                    @else
                        <div class="product-grid">
                            @foreach ($products as $product)
                                @include('partials.product-tile', ['product' => $product, 'headingTag' => 'h2'])
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="pill-tabs__panel" role="tabpanel" id="results-articles" aria-labelledby="results-tab-articles" tabindex="0" @if ($products->isNotEmpty()) hidden @endif>
                    @if ($articles->isEmpty())
                        <p class="empty-state">{{ __('No articles match your search.') }}</p>
                    @else
                        <div class="article-grid">
                            @foreach ($articles as $article)
                                @include('partials.article-tile', ['article' => $article, 'uid' => 'search'])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($q !== '')
            <div class="container">
                <p class="empty-state">{{ __('Nothing found. Try another word, or browse our products.') }}</p>
                <a class="btn btn--pink" href="{{ route('products') }}">{{ __('See all products') }}</a>
            </div>
        @endif
    </section>
@endsection
