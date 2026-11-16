{{-- Newsletter consent wording (Settings → Newsletter) followed by the data protection link. --}}
@php
    $text = str_replace('{brand}', brand(), (string) setting('newsletter.consent', ''));
    // Wording that already ends with the link phrase (in English or in the page language) would show it twice; the link follows.
    $phrase = preg_quote(__('Read our data protection policy'), '/');
    $text = trim(preg_replace('/\s*(?:Read our data protection policy|(?:សូម)?'.$phrase.')\s*[.។]?\s*$/iu', '', $text));
@endphp
{{ $text }} <a href="{{ route('page', ['slug' => 'data-protection']) }}">{{ __('Read our data protection policy') }}</a>{{ app()->getLocale() === 'km' ? '។' : '.' }}
