{{-- Splide controls: prev, page dots, next, and an optional autoplay toggle. --}}
<div class="splide__arrows carousel-controls {{ $class ?? '' }}">
    <button class="splide__arrow splide__arrow--prev" type="button">
        @include('partials.icon', ['name' => 'chevron-left', 'size' => 20])
    </button>
    <ul class="splide__pagination"></ul>
    <button class="splide__arrow splide__arrow--next" type="button">
        @include('partials.icon', ['name' => 'chevron-right', 'size' => 20])
    </button>
    @if (! empty($toggle))
        <button class="splide__toggle" type="button">
            <span class="splide__toggle__play">@include('partials.icon', ['name' => 'play', 'size' => 18])</span>
            <span class="splide__toggle__pause">@include('partials.icon', ['name' => 'pause', 'size' => 18])</span>
        </button>
    @endif
</div>
