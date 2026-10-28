{{-- How-to-use step icons from Tabler Icons (MIT): [pour] [mix] [drink] [swallow] [shake] [drops]. --}}
@php
    $glyphs = [
        'pour' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m12 3l8 4.5v9L12 21l-8-4.5v-9zm0 9l8-4.5M12 12v9m0-9L4 7.5m12-2.25l-8 4.5"/>',
        'mix' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11h14V8H5zm12.5 0L16 21H8L6.5 11M6 8V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v1m-3-3V3"/>',
        'drink' => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M8 21h8m-4-6v6m5-18l1 7c0 3.012-2.686 5-6 5s-6-1.988-6-5l1-7z"/><path d="M6 10a5 5 0 0 1 6 0a5 5 0 0 0 6 0"/></g>',
        'swallow' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.5l8-8a4.94 4.94 0 0 1 7 7l-8 8a4.94 4.94 0 0 1-7-7m4-4l7 7"/>',
        'shake' => '<g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M10 5h4V3a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1z"/><path d="M14 3.5c0 1.626.507 3.212 1.45 4.537l.05.07a8.1 8.1 0 0 1 1.5 4.694V19a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2v-6.2c0-1.682.524-3.322 1.5-4.693l.05-.07A7.82 7.82 0 0 0 10 3.5"/><path d="M7 14.803A2.4 2.4 0 0 0 8 14a2.4 2.4 0 0 1 2-1a2.4 2.4 0 0 1 2 1a2.4 2.4 0 0 0 2 1a2.4 2.4 0 0 0 2-1a2.4 2.4 0 0 1 1-.805"/></g>',
        'drops' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.502 19.423c2.602 2.105 6.395 2.105 8.996 0s3.262-5.708 1.566-8.546l-4.89-7.26c-.42-.625-1.287-.803-1.936-.397a1.4 1.4 0 0 0-.41.397l-4.893 7.26C4.24 13.715 4.9 17.318 7.502 19.423"/>',
    ];
@endphp
<svg class="step-icon" width="{{ $size ?? 56 }}" height="{{ $size ?? 56 }}" viewBox="0 0 24 24" style="--icon-stroke: 1.3" aria-hidden="true" focusable="false">{!! $glyphs[$name] ?? $glyphs['drink'] !!}</svg>
