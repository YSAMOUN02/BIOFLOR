{{-- Newsletter consent wording (Settings → Newsletter) followed by the data protection link. --}}
@php
    $text = str_replace('{brand}', brand(), (string) setting('newsletter.consent', ''));
    // Older wording carried the link phrase inside the sentence; drop it, the link follows.
    $text = trim(preg_replace('/\s*Read our data protection policy\.?\s*$/i', '', $text));
@endphp
{{ $text }} <a href="{{ route('page', ['slug' => 'data-protection']) }}">{{ __('Read our data protection policy') }}</a>.
