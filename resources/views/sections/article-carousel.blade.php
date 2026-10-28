{{-- Article carousel.
     style: default (title, intro, 3 tiles) | featured (one large story per slide) | listing (topic row) --}}
@php
    $articles = $section->articles();
    $style    = $section->get('style', 'default');
    $uid      = $section->uid();
@endphp

@if ($articles->isNotEmpty())
    @if ($style === 'featured')
        <section class="article-featured container">
            <div class="splide carousel carousel--start js-carousel" data-carousel='{"type":"slide","perPage":1,"gap":32,"pagination":true}'
                 aria-label="{{ __('Featured articles') }}">
                <div class="splide__track">
                    <ul class="splide__list">
                        @foreach ($articles as $article)
                            <li class="splide__slide">
                                <div class="featured-story card-link">
                                    <div class="featured-story__media"><img src="{{ $article->image_url }}"{{ img_size($article->image_url) }} alt="" loading="lazy"></div>
                                    <div class="featured-story__content">
                                        @if ($article->eyebrow)<span class="tag">{{ __($article->eyebrow) }}</span>@endif
                                        <h2 class="h3" id="{{ $uid }}-{{ $article->id }}">{!! $article->title !!}</h2>
                                        @if ($article->excerpt)<p class="text-intro">{{ $article->excerpt }}</p>@endif
                                        <a class="btn btn--pink" href="{{ $article->url }}" aria-describedby="{{ $uid }}-{{ $article->id }}">{{ __('Read the article') }}</a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @include('partials.carousel-controls', ['class' => 'carousel-controls--start'])
            </div>
        </section>

    @elseif ($style === 'listing')
        <section class="article-listing container">
            <div class="article-listing__intro">
                <h3>{{ $section->get('title') }}</h3>
                @if ($section->get('intro'))<p>{!! $section->get('intro') !!}</p>@endif
                @if ($section->get('cta_label'))
                    <a class="btn btn--outline" href="{{ site_url($section->get('cta_url')) }}">{{ $section->get('cta_label') }}</a>
                @endif
            </div>
            <div class="article-listing__items">
                <div class="splide carousel carousel--round js-carousel" aria-label="{{ $section->get('title') }}"
                     data-carousel='{"perPage":1,"gap":32,"pagination":false,"breakpoints":{"992":{"perPage":2,"perMove":2}}}'>
                    <div class="splide__track">
                        <ul class="splide__list">
                            @foreach ($articles as $article)
                                <li class="splide__slide">@include('partials.article-tile', ['article' => $article, 'uid' => $uid])</li>
                            @endforeach
                        </ul>
                    </div>
                    @include('partials.carousel-controls')
                </div>
            </div>
        </section>

    @else
        <section class="article-carousel container">
            @if ($section->get('title'))
                <h2 class="article-carousel__title">{!! $section->get('title') !!}</h2>
            @endif
            <div class="intro-block__row">
                @if ($section->get('intro'))
                    <p class="intro-block__text text-intro">{!! $section->get('intro') !!}</p>
                @endif
                @if ($section->get('cta_label'))
                    <a class="btn btn--pink intro-block__cta" href="{{ site_url($section->get('cta_url')) }}">{{ $section->get('cta_label') }}</a>
                @endif
            </div>

            <div class="splide carousel js-carousel" aria-label="{{ __('Articles') }}"
                 data-carousel='{"perPage":1,"perMove":1,"gap":32,"pagination":true,"breakpoints":{"992":{"perPage":3,"perMove":3,"pagination":false}}}'>
                <div class="splide__track">
                    <ul class="splide__list">
                        @foreach ($articles as $article)
                            <li class="splide__slide">@include('partials.article-tile', ['article' => $article, 'uid' => $uid])</li>
                        @endforeach
                    </ul>
                </div>
                @include('partials.carousel-controls', ['class' => 'carousel-controls--center'])
            </div>
        </section>
    @endif
@endif
