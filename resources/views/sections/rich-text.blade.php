{{-- Heading, optional subtitle and HTML body. width: full | md | 75 --}}
@php
    $tag   = in_array($section->get('title_tag'), ['h1', 'h2', 'h3'], true) ? $section->get('title_tag') : 'h2';
    $width = $section->get('width', 'full');
@endphp
<div class="container rich-text-block rich-text-block--{{ $width }}">
    @if ($section->get('title'))
        <{{ $tag }} class="rich-text-block__title {{ $tag === 'h1' ? 'h1-sm' : '' }}">{!! $section->get('title') !!}</{{ $tag }}>
    @endif
    @if ($section->get('subtitle'))
        <p class="text-subtitle">{!! $section->get('subtitle') !!}</p>
    @endif
    @if ($section->get('html'))
        <div class="rich-text">{!! $section->get('html') !!}</div>
    @endif
</div>
