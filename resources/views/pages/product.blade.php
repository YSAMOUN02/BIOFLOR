@extends('layouts.app')

@php
    $gallery   = $product->galleryUrls();
    $labels    = $product->labels();
    $badges    = $product->badges();
    $specs     = $product->specs();
    $accordion = $product->accordion();
    $steps     = $product->steps();
    $reviews   = $product->reviews;
    $average   = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : null;
    $context   = ['product' => $product];
@endphp

@section('title', $product->meta_title ?: $product->plain_name.' | '.brand())
@section('meta_description', $product->meta_description ?: plain_text($product->short_description))
@section('body_class', 'page-product')
@section('og_type', 'product')
@if ($gallery->isNotEmpty() && ! str_ends_with($gallery->first(), '.svg'))
    @section('share_image', $gallery->first())
@endif

@push('head')
    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product->plain_name,
        'sku'         => (string) $product->id,
        'image'       => $gallery->all(),
        'description' => plain_text($product->short_description),
        'brand'       => ['@type' => 'Brand', 'name' => brand()],
        'aggregateRating' => $average ? ['@type' => 'AggregateRating', 'ratingValue' => $average, 'reviewCount' => $reviews->count()] : null,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
    <div class="container">
        @include('partials.breadcrumb', ['items' => [[__('Our products'), route('products')], [$product->name, null]]])
    </div>

    {{-- ---------- main block ---------- --}}
    <div class="container pdp">
        <div class="pdp__gallery">
            @if ($gallery->count() > 1)
                <div class="splide carousel carousel--round js-carousel" aria-label="{{ __('Product images') }}"
                     data-carousel='{"type":"slide","perPage":1,"gap":16,"pagination":true}'>
                    <div class="splide__track">
                        <ul class="splide__list">
                            @foreach ($gallery as $i => $url)
                                <li class="splide__slide"><img class="pdp__image" src="{{ $url }}"{{ img_size($url) }} @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif alt="{{ $product->plain_name }} — {{ __('image :n', ['n' => $i + 1]) }}"></li>
                            @endforeach
                        </ul>
                    </div>
                    @include('partials.carousel-controls', ['class' => 'carousel-controls--center'])
                </div>
            @else
                <img class="pdp__image" src="{{ $gallery->first() }}"{{ img_size($gallery->first()) }} fetchpriority="high" alt="{{ $product->plain_name }}">
            @endif
        </div>

        <div class="pdp__info">
            <h1 class="pdp__title h1-sm">{!! $product->name !!}</h1>

            @if ($average)
                <a class="rating" href="#reviews" aria-label="{{ __('Rated :avg out of 5, :count reviews', ['avg' => $average, 'count' => $reviews->count()]) }}">
                    <span class="rating__stars" style="--rating: {{ $average }}" aria-hidden="true"></span>
                    <span class="rating__count">({{ $reviews->count() }})</span>
                </a>
            @endif

            @if ($labels->isNotEmpty())
                <p class="pdp__labels">
                    @foreach ($labels as $label)
                        <span>{{ $label }}</span>
                    @endforeach
                </p>
            @endif

            @if ($product->short_description)
                <div class="pdp__intro text-intro">{!! $product->short_description !!}</div>
            @endif

            @if ($specs->isNotEmpty())
                <dl class="pdp__specs">
                    @foreach ($specs as $name => $value)
                        <div><dt>{{ $name }}</dt><dd>{{ $value }}</dd></div>
                    @endforeach
                </dl>
            @endif

            @if ($badges->isNotEmpty())
                <ul class="pdp__badges">
                    @foreach ($badges as $badge)
                        <li class="pdp__badge">
                            @include('partials.badge-icon', ['name' => $badge['icon']])
                            <span>{{ $badge['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($accordion->isNotEmpty())
                <div class="accordion pdp__accordion">
                    @foreach ($accordion as $i => $panel)
                        <div class="accordion__item">
                            <h2 class="accordion__heading">
                                <button class="accordion__btn js-collapse" type="button" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="pdp-panel-{{ $i }}">
                                    <span>{{ $panel['title'] }}</span>
                                    @include('partials.icon', ['name' => 'chevron-down', 'size' => 16, 'class' => 'accordion__chevron'])
                                </button>
                            </h2>
                            <div class="accordion__panel rich-text {{ $panel['list'] ? 'check-list' : '' }}" id="pdp-panel-{{ $i }}" @if ($i > 0) hidden @endif>
                                {!! $panel['html'] !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @include('partials.sections', ['sections' => $sections->get('after_main', collect()), 'context' => $context])

    {{-- ---------- how to use ---------- --}}
    @if ($steps->isNotEmpty())
        <section class="container how-to-use">
            <div class="how-to-use__intro">
                <h2>{{ __('How to use') }}</h2>
                <p>{{ __('Follow these steps, and always read the package leaflet.') }}</p>
            </div>
            <ol class="how-to-use__steps">
                @foreach ($steps as $i => $step)
                    <li class="how-to-use__step">
                        <span class="how-to-use__number text-keyfact" aria-hidden="true">{{ $i + 1 }}</span>
                        <span class="how-to-use__text card-title">{{ $step['text'] }}</span>
                        @include('partials.step-icon', ['name' => $step['icon'] ?? 'drink'])
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    @include('partials.sections', ['sections' => $sections->get('middle', collect()), 'context' => $context])

    {{-- ---------- product Q&A ---------- --}}
    @if ($product->faqs->isNotEmpty())
        <section class="pdp-faq bg-blush">
            <div class="container pdp-faq__row">
                <div class="pdp-faq__intro">
                    <h2>{{ __('Your questions') }}</h2>
                    <p>{{ __('Quick answers about :name. Your pharmacist can advise you too.', ['name' => $product->plain_name]) }}</p>
                </div>
                <div class="accordion pdp-faq__list">
                    @foreach ($product->faqs as $i => $faq)
                        <div class="accordion__item">
                            <h3 class="accordion__heading">
                                <button class="accordion__btn accordion__btn--strong js-collapse" type="button" aria-expanded="false" aria-controls="pdp-faq-{{ $faq->id }}">
                                    <span>{{ $faq->question }}</span>
                                    @include('partials.icon', ['name' => 'chevron-down', 'size' => 16, 'class' => 'accordion__chevron'])
                                </button>
                            </h3>
                            <div class="accordion__panel rich-text" id="pdp-faq-{{ $faq->id }}" hidden><p>{!! $faq->answer !!}</p></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ---------- reviews ---------- --}}
    @if ($reviews->isNotEmpty())
        <section class="reviews bg-sky" id="reviews">
            <div class="container">
                <div class="reviews__head">
                    <h2>{{ __('Reviews') }}</h2>
                    <p class="reviews__summary">
                        <span class="rating__stars" style="--rating: {{ $average }}" aria-hidden="true"></span>
                        <span>{{ __(':avg / 5 — :count reviews', ['avg' => $average, 'count' => $reviews->count()]) }}</span>
                    </p>
                </div>
                <ul class="reviews__list">
                    @foreach ($reviews as $review)
                        <li class="review">
                            <span class="rating__stars" style="--rating: {{ $review->rating }}" role="img" aria-label="{{ __(':n out of 5', ['n' => $review->rating]) }}"></span>
                            @if ($review->title)<p class="review__title card-title">{{ $review->title }}</p>@endif
                            @if ($review->body)<p>{{ $review->body }}</p>@endif
                            <p class="review__meta">{{ $review->author }} · {{ $review->created_at?->isoFormat('LL') }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @include('partials.sections', ['sections' => $sections->get('bottom', collect()), 'context' => $context])

    @include('partials.references', ['id' => 'product-references', 'body' => $product->attr('references')])
@endsection
