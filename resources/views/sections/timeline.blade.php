@php
    $bg      = ['fourth' => 'bg-peach', 'fifth' => 'bg-cream', 'secondary' => 'bg-sky'][$section->get('background', 'fourth')] ?? 'bg-peach';
    // Up to four milestones fit side by side on desktop; more scroll.
    $count   = count((array) $section->get('items', []));
    $desktop = min(4, max(1, $count));
    $scroll  = $count > $desktop;
    $options = ['perPage' => 1, 'gap' => 24, 'pagination' => true,
                'breakpoints' => ['992' => ['perPage' => $desktop, 'perMove' => 1, 'pagination' => $scroll, 'arrows' => $scroll, 'drag' => $scroll]]];
@endphp
<section class="timeline {{ $bg }}">
    <div class="container">
        <h2 class="timeline__title">{!! $section->get('title') !!}</h2>
        <div class="splide carousel carousel--round js-carousel" data-carousel='@json($options)' aria-label="{{ plain_text($section->get('title')) }}">
            <div class="splide__track">
                <ul class="splide__list">
                    @foreach ((array) $section->get('items', []) as $item)
                        <li class="splide__slide">
                            <div class="timeline__item">
                                <span class="timeline__year text-keyfact">{{ $item['year'] ?? '' }}</span>
                                <span class="timeline__dot" aria-hidden="true"></span>
                                <p class="card-title">{{ $item['title'] ?? '' }}</p>
                                <p>{!! $item['text'] ?? '' !!}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            @include('partials.carousel-controls')
        </div>
    </div>
</section>
