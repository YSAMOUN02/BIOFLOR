@php
    $bg = ['secondary' => 'bg-sky', 'fourth' => 'bg-peach', 'fifth' => 'bg-cream', 'third' => 'bg-pink'][$section->get('background', 'secondary')] ?? 'bg-sky';
    $styles = ['pink' => 'btn--pink', 'navy' => 'btn--navy', 'outline' => 'btn--outline'];
@endphp
<div class="container">
    <div class="cta-box {{ $bg }}">
        <h2>{!! $section->get('title') !!}</h2>
        @if ($section->get('text'))
            <p class="text-intro">{!! $section->get('text') !!}</p>
        @endif
        <div class="cta-box__buttons">
            @foreach ((array) $section->get('buttons', []) as $button)
                <a class="btn {{ $styles[$button['style'] ?? 'pink'] ?? 'btn--pink' }}" href="{{ site_url($button['url'] ?? '#') }}">{{ $button['label'] ?? '' }}</a>
            @endforeach
        </div>
    </div>
</div>
