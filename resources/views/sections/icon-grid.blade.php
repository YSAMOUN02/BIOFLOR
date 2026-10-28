{{-- Icon + title + text items. carousel: true → swipeable on mobile, 4 in a row on desktop. --}}
@php
    $backgrounds = ['primary' => 'bg-navy', 'fourth' => 'bg-peach', 'fifth' => 'bg-cream', 'secondary' => 'bg-sky', 'sixth' => 'bg-blush'];
    $bg      = $backgrounds[$section->get('background', 'fifth')] ?? '';
    $dark    = $bg === 'bg-navy';
    $items   = array_values((array) $section->get('items', []));
    $options = ['perPage' => 1, 'gap' => 24, 'pagination' => true,
                'breakpoints' => ['992' => ['perPage' => min(4, max(1, count($items))), 'pagination' => false, 'arrows' => false, 'drag' => false]]];
@endphp
<section class="icon-grid {{ $bg }} {{ $dark ? 'is-dark' : '' }}">
    <div class="container">
        @if ($section->get('title'))
            <h2 class="icon-grid__title">{!! $section->get('title') !!}</h2>
        @endif
        @if ($section->get('intro'))
            <p class="text-intro icon-grid__intro">{!! $section->get('intro') !!}</p>
        @endif

        @if ($section->get('carousel'))
            <div class="splide carousel {{ $dark ? 'carousel--light' : '' }} js-carousel" data-carousel='@json($options)' aria-label="{{ plain_text($section->get('title')) }}">
                <div class="splide__track">
                    <ul class="splide__list">
                        @foreach ($items as $item)
                            <li class="splide__slide">@include('sections.partials.icon-item', ['item' => $item])</li>
                        @endforeach
                    </ul>
                </div>
                @include('partials.carousel-controls', ['class' => 'carousel-controls--center'])
            </div>
        @else
            <ul class="icon-grid__list">
                @foreach ($items as $item)
                    <li>@include('sections.partials.icon-item', ['item' => $item])</li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
