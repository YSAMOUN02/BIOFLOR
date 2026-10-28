<div class="icon-item">
    @if (! empty($item['icon']))
        <span class="icon-item__icon" aria-hidden="true"><img src="{{ media_url($item['icon']) }}"{{ img_size(media_url($item['icon'])) }} alt=""></span>
    @endif
    <p class="icon-item__title card-title">{!! $item['title'] ?? '' !!}</p>
    @if (! empty($item['text']))
        <p class="icon-item__text">{!! $item['text'] !!}</p>
    @endif
</div>
