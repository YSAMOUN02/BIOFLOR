@php
    $slides  = array_values((array) $section->get('slides', []));
    $several = count($slides) > 1;
    $options = $several
        ? ['type' => 'loop', 'perPage' => 1, 'gap' => 32, 'autoplay' => true, 'interval' => (int) $section->get('interval', 6000), 'pauseOnHover' => true]
        : ['type' => 'slide', 'perPage' => 1, 'arrows' => false, 'pagination' => false, 'drag' => false];
@endphp

<section class="hero-carousel bg-navy is-dark" aria-label="{{ __('Highlights') }}">
    <div class="container">
        <div class="splide carousel carousel--light js-carousel" data-carousel='@json($options)' aria-label="{{ __('Highlights') }}">
            <div class="splide__track">
                <ul class="splide__list">
                    @foreach ($slides as $i => $slide)
                        @php $titleId = $section->uid('slide-'.$i); @endphp
                        <li class="splide__slide">
                            <div class="hero-slide">
                                <div class="hero-slide__content">
                                    <h2 class="hero-slide__title h1" id="{{ $titleId }}">{!! $slide['title'] ?? '' !!}</h2>
                                    @if (! empty($slide['text']))
                                        <div class="hero-slide__text text-intro">{!! $slide['text'] !!}</div>
                                    @endif
                                    @if (! empty($slide['cta_label']))
                                        <a class="btn btn--pink" href="{{ site_url($slide['cta_url'] ?? '#') }}" aria-describedby="{{ $titleId }}"
                                           @if (! empty($slide['new_tab'])) target="_blank" rel="noopener" @endif>{{ $slide['cta_label'] }}</a>
                                    @endif
                                </div>
                                {{-- shape "plain": the image is already cut to shape (transparent PNG/WebP) --}}
                                <div class="hero-slide__media {{ ($slide['shape'] ?? 'blob') === 'plain' ? 'hero-slide__media--plain' : 'media-blob' }}">
                                    <picture>
                                        @if (! empty($slide['image_mobile']))
                                            <source srcset="{{ media_url($slide['image']) }}" media="(min-width: 992px)">
                                        @endif
                                        <img src="{{ media_url($slide['image_mobile'] ?? null) ?? media_url($slide['image'] ?? null) }}"{{ img_size(media_url($slide['image_mobile'] ?? null) ?? media_url($slide['image'] ?? null)) }}
                                             alt="{{ $slide['image_alt'] ?? '' }}" @if ($i > 0) loading="lazy" @else fetchpriority="high" @endif>
                                    </picture>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($several)
                @include('partials.carousel-controls', ['toggle' => true, 'class' => 'carousel-controls--start'])
            @endif
        </div>
    </div>
</section>
