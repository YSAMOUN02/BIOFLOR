<section class="category-banner bg-gradient-2 is-dark">
    <div class="container category-banner__row">
        <div class="category-banner__content">
            @include('partials.breadcrumb', ['items' => [[$section->get('title'), null]], 'class' => 'breadcrumb--light'])
            <h1 class="h1-sm">{!! $section->get('title') !!}</h1>
            @if ($section->get('text'))
                <p class="text-intro">{!! $section->get('text') !!}</p>
            @endif
        </div>
        @if ($section->get('image'))
            <div class="category-banner__media" aria-hidden="true">
                <img src="{{ media_url($section->get('image')) }}"{{ img_size(media_url($section->get('image'))) }} alt="">
            </div>
        @endif
    </div>
</section>
