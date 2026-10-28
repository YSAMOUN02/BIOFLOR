{{-- Badge for yes/no product attributes: a circle with a Tabler Icons glyph (MIT). --}}
@php
    $glyphs = [
        'gmo-free' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 3v1q-.012 3.69-2.705 6.281M12 12c-3.328 1.99-5 4.662-5.008 8.014v1m10.008 0v-1c0-1.44-.315-2.755-.932-3.944M12 12c-1.903-1.138-3.263-2.485-4.082-4.068M8 4h9M7 20h10M12 8h4m-8 8h8M3 3l18 18"/>',
        'gluten-free' => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="m3 3l18 18m-9 .5v-3.75M5.916 9.49l-.43 1.604a4.984 4.984 0 0 0 3.524 6.104L12 18v-3.44a4.98 4.98 0 0 0-3.677-4.426zm4.333-5.239l.021-.021L12 2.5"/><path d="M10.27 11.15a4.9 4.9 0 0 1-1.246-2.118m5.964-.044A4.9 4.9 0 0 0 13.73 4.23L12 2.5m4.038 7.537l2.046-.547l.431 1.604c.142.53.193 1.063.162 1.583m-2.171 3.828c-.45.307-.959.544-1.516.694L12 18v-3.44a5 5 0 0 1 .582-1.978"/></g>',
        'vegetarian' => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M5 21c.5-4.5 2.5-8 7-10"/><path d="M9 18c6.218 0 10.5-3.288 11-12V4h-4.014c-9 0-11.986 4-12 9c0 1 0 3 2 5z"/></g>',
        'lactose-free' => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M10 6h6V4a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1m8 2l1.094 1.759a6 6 0 0 1 .906 3.17V14m0 4v1a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-8.071a6 6 0 0 1 .906-3.17l.327-.525"/><path d="M10 16a2 2 0 1 0 4 0a2 2 0 1 0-4 0M3 3l18 18"/></g>',
        'check' => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0-18 0"/><path d="m9 12l2 2l4-4"/></g>',
    ];
@endphp
<svg class="badge-icon" width="{{ $size ?? 72 }}" height="{{ $size ?? 72 }}" viewBox="0 0 64 64" aria-hidden="true" focusable="false">
    <circle cx="32" cy="32" r="30" fill="none" stroke="currentColor" stroke-width="2"/>
    <svg x="16" y="16" width="32" height="32" viewBox="0 0 24 24" style="--icon-stroke: 1.6">{!! $glyphs[$name] ?? $glyphs['check'] !!}</svg>
</svg>
