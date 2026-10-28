{{-- Brand logo: the uploaded file when there is one, otherwise a text wordmark
     built from the brand settings. $variant = 'dark' (header) | 'light' (footer). --}}
@php
    $variant = $variant ?? 'dark';
    $upload  = setting($variant === 'light' ? 'brand.logo_footer' : 'brand.logo');
    $strain  = trim((string) setting('brand.strain', ''));
    // "Saccharomyces boulardii CNCM I-745" → species in italics, strain code boxed
    [$species, $code] = preg_match('/^(\S+\s+\S+)\s+(.+)$/u', $strain, $m) ? [$m[1], $m[2]] : [$strain, ''];
@endphp
@if ($upload)
    <img class="logo-image" src="{{ media_url($upload) }}" alt="{{ brand() }}" height="56">
@else
    <span class="wordmark wordmark--{{ $variant }}" role="img" aria-label="{{ brand() }}">
        <span class="wordmark__name" aria-hidden="true">{{ mb_strtoupper(brand()) }}<sup>&reg;</sup></span>
        @if ($strain)
            <span class="wordmark__strap" aria-hidden="true">
                <i>{{ $species }}</i>
                @if ($code) <b>{{ $code }}</b> @endif
            </span>
        @endif
    </span>
@endif
