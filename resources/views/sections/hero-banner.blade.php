{{-- Generic banner: text + image.
     variant:     informative | testimonial | highlight
     align:       left (text first) | right (image first)
     background:  secondary | primary | gradient-first | gradient-third | fourth | fifth | none
     full_width:  colour spans the page (otherwise a boxed card inside the container)
     image_shape: blob | square
     title_style: quote | h1 | (default h2) --}}
@php
    $backgrounds = [
        'secondary' => 'bg-sky', 'primary' => 'bg-navy', 'gradient-first' => 'bg-gradient-1',
        'gradient-third' => 'bg-gradient-3', 'fourth' => 'bg-peach', 'fifth' => 'bg-cream', 'none' => '',
    ];
    $bg       = $backgrounds[$section->get('background', 'none')] ?? '';
    $dark     = in_array($bg, ['bg-navy', 'bg-gradient-1', 'bg-gradient-3'], true);
    $full     = (bool) $section->get('full_width');
    $variant  = $section->get('variant', 'informative');
    $align    = $section->get('align', 'left') === 'right' ? 'right' : 'left';
    $shape    = $section->get('image_shape', 'square');
    $titleTag = in_array($section->get('title_tag'), ['h1', 'h2', 'h3'], true) ? $section->get('title_tag') : 'h2';
    $titleCls = ['quote' => 'text-quote', 'h1' => 'h1'][$section->get('title_style')] ?? '';
    $titleId  = $section->uid('title');

    // On product pages a product attribute may replace the shared text.
    $text = $section->get('text');
    if (isset($product) && $section->get('product_text') && $product->attr($section->get('product_text'))) {
        $text = $product->attr($section->get('product_text'));
    }
@endphp

<section class="banner-section {{ $full ? $bg.' banner-section--full' : '' }} {{ $dark ? 'is-dark' : '' }}">
    <div class="container">
        @if ($section->get('breadcrumb'))
            @include('partials.breadcrumb', ['items' => [[$section->get('breadcrumb'), null]], 'class' => 'breadcrumb--light'])
        @endif

        <div class="banner banner--{{ $variant }} banner--{{ $align }} {{ $full ? '' : $bg }} {{ $section->get('cta_label') ? 'card-link' : '' }} {{ $shape === 'square' ? 'image-zoom' : '' }}">
            <div class="banner__content">
                <div>
                    @if ($section->get('surtitle'))
                        <p class="banner__surtitle">{{ $section->get('surtitle') }}</p>
                    @endif
                    <{{ $titleTag }} class="banner__title {{ $titleCls }}" id="{{ $titleId }}">{!! $section->get('title') !!}</{{ $titleTag }}>
                    @if ($text)
                        <div class="banner__text {{ $dark ? 'text-intro' : '' }} {{ $section->get('list_style') === 'check' ? 'check-list' : '' }}">{!! $text !!}</div>
                    @endif
                </div>
                @if ($section->get('cta_label'))
                    <a class="btn btn--pink" href="{{ site_url($section->get('cta_url')) }}" aria-describedby="{{ $titleId }}">{{ $section->get('cta_label') }}</a>
                @endif
            </div>

            @if ($section->get('image'))
                <div class="banner__media banner__media--{{ $shape }} {{ $shape === 'blob' ? 'media-blob' : '' }}">
                    <img src="{{ media_url($section->get('image')) }}"{{ img_size(media_url($section->get('image'))) }} alt="" loading="lazy">
                </div>
            @endif
        </div>
    </div>
</section>
