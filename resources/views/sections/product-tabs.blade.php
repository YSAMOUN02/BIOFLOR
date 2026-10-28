{{-- Product range as pill tabs ("All", then one tab per child category).
     layout: carousel (3 per view on desktop) | grid --}}
@php
    $tabs    = $section->productTabs();
    $layout  = $section->get('layout', 'carousel');
    $uid     = $section->uid();
    $options = ['perPage' => 1, 'perMove' => 1, 'gap' => 32, 'pagination' => false,
                'breakpoints' => ['992' => ['perPage' => 3, 'perMove' => 3]]];
@endphp

@if ($tabs->isNotEmpty())
    <div class="container product-tabs js-tabs">
        <div class="pill-tabs" role="tablist" aria-label="{{ __('Product formats') }}">
            @foreach ($tabs as $i => $tab)
                <button class="pill-tabs__btn" type="button" role="tab" id="{{ $uid }}-tab-{{ $tab['id'] }}"
                        aria-controls="{{ $uid }}-panel-{{ $tab['id'] }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                        tabindex="{{ $i === 0 ? '0' : '-1' }}">{{ $tab['label'] }}</button>
            @endforeach
        </div>

        @foreach ($tabs as $i => $tab)
            <div class="pill-tabs__panel" role="tabpanel" id="{{ $uid }}-panel-{{ $tab['id'] }}"
                 aria-labelledby="{{ $uid }}-tab-{{ $tab['id'] }}" tabindex="0" @if ($i > 0) hidden @endif>
                @if ($layout === 'grid')
                    <div class="product-grid">
                        @foreach ($tab['products'] as $product)
                            @include('partials.product-tile', ['product' => $product])
                        @endforeach
                    </div>
                @else
                    <div class="splide carousel carousel--round js-carousel" data-carousel='@json($options)' aria-label="{{ $tab['label'] }}">
                        <div class="splide__track">
                            <ul class="splide__list">
                                @foreach ($tab['products'] as $product)
                                    <li class="splide__slide">@include('partials.product-tile', ['product' => $product])</li>
                                @endforeach
                            </ul>
                        </div>
                        @include('partials.carousel-controls')
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
