@php $titleId = $section->uid('title'); @endphp
<section class="feature-block bg-navy is-dark">
    <div class="container feature-block__row">
        <div class="feature-block__intro">
            <h2>{!! $section->get('title') !!}</h2>
            @if ($section->get('text'))
                <p class="text-intro">{!! $section->get('text') !!}</p>
            @endif
        </div>

        <div class="feature-block__item">
            @if ($section->get('image'))
                <img class="feature-block__image" src="{{ media_url($section->get('image')) }}"{{ img_size(media_url($section->get('image'))) }} alt="" loading="lazy">
            @endif
            <div class="feature-block__copy">
                <h3 id="{{ $titleId }}">{!! $section->get('item_title') !!}</h3>
                <p>{!! $section->get('item_text') !!}</p>
                @if ($section->get('cta_label'))
                    <a class="btn btn--pink" href="{{ site_url($section->get('cta_url')) }}" aria-describedby="{{ $titleId }}">{{ $section->get('cta_label') }}</a>
                @endif
            </div>
        </div>
    </div>
</section>
