<div class="container intro-block">
    @if ($section->get('title'))
        <h2 class="intro-block__title">{!! $section->get('title') !!}</h2>
    @endif
    <div class="intro-block__row">
        @if ($section->get('text'))
            <p class="intro-block__text text-intro">{!! $section->get('text') !!}</p>
        @endif
        @if ($section->get('cta_label'))
            <a class="btn btn--pink intro-block__cta" href="{{ site_url($section->get('cta_url')) }}">{{ $section->get('cta_label') }}</a>
        @endif
    </div>
</div>
