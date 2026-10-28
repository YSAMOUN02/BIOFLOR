{{-- Indications: coloured blob icons with a title and a "Learn more" link.
     One per view on mobile, up to five in a row with dividers on desktop.
     An item without a link shows its title only. --}}
@php
    $items   = array_values((array) $section->get('items', []));
    $desktop = min(5, max(1, count($items)));
    $scroll  = count($items) > $desktop;
    $options = ['perPage' => 1, 'perMove' => 1, 'gap' => 24, 'pagination' => true,
                'breakpoints' => ['992' => ['perPage' => $desktop, 'pagination' => $scroll, 'arrows' => $scroll, 'drag' => $scroll]]];
@endphp

<section class="indications bg-gradient-1 is-dark {{ $desktop > 4 ? 'indications--five' : '' }}">
    <div class="container">
        <h2 class="indications__title">{!! $section->get('title') !!}</h2>

        <div class="splide carousel carousel--light js-carousel" data-carousel='@json($options)' aria-label="{{ plain_text($section->get('title')) }}">
            <div class="splide__track">
                <ul class="splide__list">
                    @foreach ($items as $i => $item)
                        @php
                            $titleId = $section->uid('item-'.$i);
                            $url     = filled($item['url'] ?? null) ? site_url($item['url']) : null;
                            $tag     = $url ? 'a' : 'div';
                        @endphp
                        <li class="splide__slide">
                            <div class="indication">
                                <div class="indication__top">
                                    <{{ $tag }} class="indication__icon indication__icon--{{ $item['color'] ?? 'magenta' }}"
                                       @if ($url) href="{{ $url }}" tabindex="-1" @endif aria-hidden="true">
                                        <img src="{{ media_url($item['icon'] ?? null) }}"{{ img_size(media_url($item['icon'] ?? null)) }} alt="">
                                    </{{ $tag }}>
                                    <{{ $tag }} class="indication__title" @if ($url) href="{{ $url }}" @endif id="{{ $titleId }}">{!! $item['title'] ?? '' !!}</{{ $tag }}>
                                </div>
                                @if ($url)
                                    <a class="btn btn--outline-white" href="{{ $url }}" aria-describedby="{{ $titleId }}">
                                        {{ $item['cta_label'] ?? __('Learn more') }}
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            @include('partials.carousel-controls', ['class' => 'carousel-controls--center'])
        </div>
    </div>
</section>
