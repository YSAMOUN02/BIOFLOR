{{-- Wide video still with a play button that opens the film in a dialog.
     video_url: an .mp4/.webm file, or a YouTube / Vimeo page link. --}}
@php
    $video   = trim((string) $section->get('video_url'));
    $modalId = $section->uid('video');
    $textId  = $section->uid('transcript');
    $embed   = null;

    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]{6,})#', $video, $m)) {
        $embed = 'https://www.youtube-nocookie.com/embed/'.$m[1].'?autoplay=1&rel=0';
    } elseif (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $video, $m) && ! preg_match('#\.(mp4|webm|ogg)#i', $video)) {
        $embed = 'https://player.vimeo.com/video/'.$m[1].'?autoplay=1';
    }
@endphp

<section class="tv-spot">
    <div class="tv-spot__media">
        @if ($section->get('image'))
            <img src="{{ media_url($section->get('image')) }}"{{ img_size(media_url($section->get('image'))) }} alt="" loading="lazy">
        @elseif ($video !== '' && ! $embed)
            {{-- No still image: the film's first frames serve as the poster. --}}
            <video class="tv-spot__poster" src="{{ media_url($video) }}#t=0.5" muted playsinline preload="metadata" aria-hidden="true" tabindex="-1"></video>
        @endif

        @if ($video !== '')
            <button class="tv-spot__play js-modal-open" type="button" aria-controls="{{ $modalId }}" aria-haspopup="dialog">
                <svg width="80" height="71" viewBox="0 0 80 71" aria-hidden="true" focusable="false">
                    <path class="tv-spot__play-bg" d="M42 2c20 0 37 13 37 32 0 21-19 35-40 35C17 69 1 55 1 36 1 17 21 2 42 2z"/>
                    <path class="tv-spot__play-icon" d="M32 24v22l18-11z"/>
                </svg>
                <span class="visually-hidden">{{ __('Play the video in a dialog') }}</span>
            </button>
        @endif
    </div>

    <div class="container">
        <div class="tv-spot__meta">
            @if ($section->get('legend'))
                <p class="tv-spot__legend">{{ $section->get('legend') }}</p>
            @endif
            @if ($section->get('transcript'))
                <button class="tv-spot__transcript-btn js-collapse" type="button" aria-expanded="false" aria-controls="{{ $textId }}">
                    <span data-when="closed">{{ __('See the text transcript') }}</span>
                    <span data-when="open">{{ __('Hide the text transcript') }}</span>
                    @include('partials.icon', ['name' => 'chevron-down', 'size' => 16, 'class' => 'tv-spot__chevron'])
                </button>
            @endif
        </div>

        @if ($section->get('transcript'))
            <div class="tv-spot__transcript" id="{{ $textId }}" hidden>
                <div class="rich-text">
                    @if ($section->get('transcript_title'))
                        <h3>{{ $section->get('transcript_title') }}</h3>
                    @endif
                    {!! $section->get('transcript') !!}
                </div>
            </div>
        @endif
    </div>

    @if ($video !== '')
        <div class="modal modal--video js-modal" id="{{ $modalId }}" role="dialog" aria-modal="true" aria-label="{{ $section->get('legend', __('Video')) }}" hidden>
            <div class="modal__dialog">
                <button class="icon-btn modal__close js-modal-close" type="button" aria-label="{{ __('Close dialog') }}">
                    @include('partials.icon', ['name' => 'close', 'size' => 18, 'stroke' => 2])
                </button>
                @if ($embed)
                    <div class="modal__frame" data-src="{{ $embed }}"></div>
                @else
                    <video class="modal__video" controls playsinline preload="none">
                        <source src="{{ media_url($video) }}">
                    </video>
                @endif
            </div>
        </div>
    @endif
</section>
