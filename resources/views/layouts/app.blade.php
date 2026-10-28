@php
    $localeModel = app()->bound('storefront.locale_model') ? app('storefront.locale_model') : null;
    // @section('title', …) has already escaped its text; decode it so {{ }} below escapes it once.
    $yielded     = fn (string $name) => html_entity_decode(trim($__env->yieldContent($name)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageTitle   = $yielded('title') ?: str_replace('{brand}', brand(), (string) setting('site.title', '{brand} | Official site'));
    $metaDesc    = $yielded('meta_description') ?: str_replace('{brand}', brand(), (string) setting('site.meta_description', ''));
    $shareImage  = $yielded('share_image') ?: media_url(setting('site.share_image')) ?: media_url(setting('brand.logo'));

    // The same page in each language, for search engines (hreflang). Languages
    // not yet translated are left out and marked noindex.
    $noindexLocales = (array) config('storefront.noindex_locales', []);
    $currentPrefix  = app()->getLocale();
    $alternates     = collect($globalLocales ?? [])
        ->reject(fn ($l) => in_array($l->url_prefix, $noindexLocales, true))
        ->mapWithKeys(fn ($l) => [str_replace('_', '-', $l->code) => url(preg_replace('#^/?[a-z]{2}(?=/|$)#', $l->url_prefix, request()->path()))]);
    $robots = trim($__env->yieldContent('robots')) ?: (in_array($currentPrefix, $noindexLocales, true) ? 'noindex, follow' : null);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $localeModel->code ?? 'en_US') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#001660">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    @if ($robots)
        <meta name="robots" content="{{ $robots }}">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
        @if ($alternates->count() > 1)
            @foreach ($alternates as $hreflang => $href)
                <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
            @endforeach
            <link rel="alternate" hreflang="x-default" href="{{ url(preg_replace('#^/?[a-z]{2}(?=/|$)#', config('storefront.default_locale'), request()->path())) }}">
        @endif
    @endif

    {{-- Link previews on Facebook, TikTok, Telegram and other apps. --}}
    <meta property="og:site_name" content="{{ brand() }}">
    <meta property="og:type" content="{{ trim($__env->yieldContent('og_type')) ?: 'website' }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ $localeModel->code ?? 'en_US' }}">
    @if ($shareImage)
        <meta property="og:image" content="{{ $shareImage }}">
    @endif
    <meta name="twitter:card" content="{{ $shareImage ? 'summary_large_image' : 'summary' }}">

    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,400;0,700;1,400;1,700&family=Staatliches&display=swap">
    @if (app()->getLocale() === 'km')
        {{-- Khmer script: Noto Serif Khmer (OFL) for text and headings. --}}
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+Khmer:wght@400;600;700&display=swap">
    @endif
    <link rel="stylesheet" href="{{ asset('vendor/splide/splide-core.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    <script type="application/ld+json">
    {!! json_encode([
        [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => brand(),
            'url'      => route('home'),
            'logo'     => setting('brand.logo') ? media_url(setting('brand.logo')) : asset('img/favicon.svg'),
        ],
        [
            '@context'        => 'https://schema.org',
            '@type'           => 'WebSite',
            'name'            => brand(),
            'url'             => route('home'),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => route('search').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
    @stack('head')
</head>
<body class="@yield('body_class')">
    <a class="skip-link" href="#main-navigation">{{ __('Skip to main navigation') }}</a>
    <a class="skip-link" href="#main-content">{{ __('Skip to main content') }}</a>
    <a class="skip-link" href="#footer-content">{{ __('Skip to footer content') }}</a>

    @include('partials.header')

    <main id="main-content">
        @yield('content')

        @unless (trim($__env->yieldContent('hide_newsletter')))
            @include('partials.newsletter')
        @endunless
    </main>

    @include('partials.footer')
    @include('partials.newsletter-popup')

    <script src="{{ asset('vendor/splide/splide.min.js') }}" defer></script>
    <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>
