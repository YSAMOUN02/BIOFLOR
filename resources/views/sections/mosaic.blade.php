{{-- Two columns: HTML on one side, an image or a second HTML block on the other. --}}
<div class="container mosaic {{ $section->get('image_side') === 'left' ? 'mosaic--reverse' : '' }}">
    <div class="mosaic__main rich-text">{!! $section->get('html') !!}</div>
    <div class="mosaic__aside">
        @if ($section->get('image'))
            <img class="mosaic__image" src="{{ media_url($section->get('image')) }}"{{ img_size(media_url($section->get('image'))) }} alt="" loading="lazy">
        @else
            <div class="rich-text">{!! $section->get('aside') !!}</div>
        @endif
    </div>
</div>
