{{-- Highlight card (wide) above two learn cards. Every card is clickable through its button. --}}
@php
    $highlight = $section->get('highlight');
    $cards     = array_values((array) $section->get('cards', []));
@endphp

<section class="learn-grid {{ $section->get('bubbles') ? 'has-bubbles' : '' }}">
    <div class="container">
        @if ($highlight)
            @php $hid = $section->uid('highlight'); @endphp
            <div class="highlight-card bg-cream card-link image-zoom">
                <div class="highlight-card__content">
                    <h2 id="{{ $hid }}">{!! $highlight['title'] ?? '' !!}</h2>
                    @if (! empty($highlight['cta_label']))
                        <a class="btn btn--pink" href="{{ site_url($highlight['cta_url'] ?? '#') }}" aria-describedby="{{ $hid }}">{{ $highlight['cta_label'] }}</a>
                    @endif
                </div>
                <div class="highlight-card__media">
                    <img src="{{ media_url($highlight['image'] ?? null) }}"{{ img_size(media_url($highlight['image'] ?? null)) }} alt="" loading="lazy">
                </div>
            </div>
        @endif

        @if ($cards)
            <div class="learn-grid__cards">
                @foreach ($cards as $i => $card)
                    @php $cid = $section->uid('card-'.$i); @endphp
                    <div class="learn-card card-link image-zoom">
                        <div class="learn-card__media">
                            <img src="{{ media_url($card['image'] ?? null) }}"{{ img_size(media_url($card['image'] ?? null)) }} alt="" loading="lazy">
                        </div>
                        <div class="learn-card__content">
                            <h2 class="h3" id="{{ $cid }}">{!! $card['title'] ?? '' !!}</h2>
                            @if (! empty($card['text']))
                                <p>{!! $card['text'] !!}</p>
                            @endif
                            @if (! empty($card['cta_label']))
                                <a class="btn btn--pink" href="{{ site_url($card['cta_url'] ?? '#') }}" aria-describedby="{{ $cid }}">{{ $card['cta_label'] }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
