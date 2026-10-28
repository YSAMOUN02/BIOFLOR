@extends('layouts.app')

@php
    $hubs = [
        'gut-health'     => [__('Gut health'), route('page', ['slug' => 'gut-health'])],
        'our-uniqueness' => [__('Our uniqueness'), route('page', ['slug' => 'our-uniqueness'])],
        'news'           => [__('Our news'), route('page', ['slug' => 'our-news'])],
    ];
    $crumbs = array_values(array_filter([$hubs[$article->category] ?? null, [$article->title, null]]));
@endphp

@section('title', plain_text($article->title).' | '.brand())
@section('meta_description', (string) $article->excerpt)
@section('body_class', 'page-article')
@section('og_type', 'article')
@unless (str_ends_with($article->image_url, '.svg'))
    @section('share_image', $article->image_url)
@endunless

@section('content')
    <section class="article-header bg-navy is-dark">
        <div class="container">
            @include('partials.breadcrumb', ['items' => $crumbs, 'class' => 'breadcrumb--light'])
            <div class="article-header__row">
                <div class="article-header__content">
                    @if ($article->eyebrow)<span class="tag tag--light">{{ __($article->eyebrow) }}</span>@endif
                    <h1 class="h1-sm">{!! $article->title !!}</h1>
                    @if ($article->excerpt)<p class="text-intro">{{ $article->excerpt }}</p>@endif
                    <p class="article-header__meta">
                        @if ($article->published_at)<span>{{ $article->published_at->isoFormat('LL') }}</span>@endif
                        <span>{{ trans_choice(':count min read|:count min read', $article->read_minutes, ['count' => $article->read_minutes]) }}</span>
                    </p>
                </div>
                <div class="article-header__media media-blob">
                    <img src="{{ $article->image_url }}"{{ img_size($article->image_url) }} alt="">
                </div>
            </div>
        </div>
    </section>

    <article class="container article-body rich-text">
        {!! str_replace('{brand}', brand(), (string) $article->body) !!}
    </article>

    <div class="container">
        <div class="cta-box bg-sky">
            <h2>{{ __('Find the right :brand product', ['brand' => brand()]) }}</h2>
            <p class="text-intro">{{ __('Ask your pharmacist, and discover the formats for every age.') }}</p>
            <div class="cta-box__buttons">
                <a class="btn btn--pink" href="{{ route('products') }}">{{ __('Our products') }}</a>
                <a class="btn btn--outline" href="{{ route('where-to-buy') }}">{{ setting('header.where_to_buy', __('Where to buy')) }}</a>
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="article-related">
            <div class="container">
                <h2 class="article-carousel__title">{{ __('You may also like') }}</h2>
                <div class="splide carousel js-carousel" aria-label="{{ __('Related articles') }}"
                     data-carousel='{"perPage":1,"gap":32,"pagination":true,"breakpoints":{"992":{"perPage":3,"perMove":3,"pagination":false}}}'>
                    <div class="splide__track">
                        <ul class="splide__list">
                            @foreach ($related as $item)
                                <li class="splide__slide">@include('partials.article-tile', ['article' => $item, 'uid' => 'related'])</li>
                            @endforeach
                        </ul>
                    </div>
                    @include('partials.carousel-controls', ['class' => 'carousel-controls--center'])
                </div>
            </div>
        </section>
    @endif

    @include('partials.references', ['id' => 'article-references', 'body' => $article->references])
@endsection
