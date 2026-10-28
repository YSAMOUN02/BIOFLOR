<section class="key-figures bg-peach" aria-label="{{ __('Key figures') }}">
    <div class="container">
        <ul class="key-figures__list">
            @foreach ((array) $section->get('items', []) as $item)
                <li class="key-figure">
                    <span class="key-figure__icon" aria-hidden="true">
                        @if (! empty($item['icon']))
                            <img src="{{ media_url($item['icon']) }}"{{ img_size(media_url($item['icon'])) }} alt="">
                        @endif
                    </span>
                    <span class="key-figure__text">
                        <span class="key-figure__value text-keyfact">{{ $item['value'] ?? '' }}</span>
                        <span class="key-figure__label">{!! $item['label'] ?? '' !!}@if (! empty($item['ref']))<sup>{{ $item['ref'] }}</sup>@endif</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
