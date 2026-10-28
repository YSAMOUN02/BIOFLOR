{{-- Product tile. Whole card is clickable through the name link. --}}
@php
    $tag    = $headingTag ?? 'h3';
    $labels = $product->labels()->take(2);
@endphp
<div class="product-tile">
    @if ($product->attr('badge'))
        <span class="product-tile__badge">{{ $product->attr('badge') }}</span>
    @endif

    <div class="product-tile__body">
        <div class="product-tile__image">
            <img src="{{ $product->image_url }}"{{ img_size($product->image_url) }} alt="{{ $product->plain_name }}" loading="lazy">
        </div>
        <{{ $tag }} class="product-tile__name">
            <a class="product-tile__link" href="{{ $product->url }}">{!! $product->name !!}</a>
        </{{ $tag }}>
    </div>

    <div class="product-tile__footer">
        <div class="product-tile__labels">
            @foreach ($labels as $label)
                <span>{{ $label }}</span>
            @endforeach
        </div>
        @include('partials.icon', ['name' => 'arrow-right', 'size' => 20, 'class' => 'product-tile__arrow'])
    </div>
</div>
