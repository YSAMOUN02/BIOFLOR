<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Site setting from the settings table (admin → Settings). */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('content_locale')) {
    /** Language of the current page for translated content: '' for the default language, else its prefix (km, fr). */
    function content_locale(): string
    {
        $locale = app()->bound('storefront.locale') ? (string) app('storefront.locale') : '';

        return $locale === '' || $locale === config('storefront.default_locale') ? '' : $locale;
    }
}

if (! function_exists('brand')) {
    /** Brand name used across the site, e.g. "Bioflor". */
    function brand(): string
    {
        return (string) setting('brand.name', config('storefront.brand', 'Bioflor'));
    }
}

if (! function_exists('media_url')) {
    /**
     * Resolve an image reference from content: absolute URLs pass through,
     * "uploads/..." and "img/..." are served from public/.
     */
    function media_url(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }

        $path = ltrim($path, '/');

        return asset(str_starts_with($path, 'img/') || str_starts_with($path, 'uploads/') ? $path : 'uploads/'.$path);
    }
}

if (! function_exists('img_size')) {
    /**
     * ` width="…" height="…"` for an image on this site (a content reference
     * or a URL from media_url()), so the browser keeps its space before it
     * loads and the page does not jump. Empty for SVG, remote or unreadable files.
     */
    function img_size(?string $path): Illuminate\Support\HtmlString
    {
        static $sizes = [];

        $path = (string) $path;
        $base = rtrim(asset(''), '/').'/';
        if (str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        } elseif (preg_match('#^(https?:|//|data:)#', $path)) {
            return new Illuminate\Support\HtmlString('');
        }

        $path = ltrim(strtok($path, '?#') ?: '', '/');
        if ($path !== '' && ! str_starts_with($path, 'img/') && ! str_starts_with($path, 'uploads/')) {
            $path = 'uploads/'.$path;
        }

        // Photos only (JPEG, PNG, WebP, AVIF): SVG icons are sized by the stylesheet.
        if (! array_key_exists($path, $sizes)) {
            $file = public_path($path);
            $size = $path !== '' && ! str_ends_with($path, '.svg') && is_file($file) ? @getimagesize($file) : false;

            $sizes[$path] = $size && $size[0] > 0 && $size[1] > 0 ? [$size[0], $size[1]] : null;
        }

        return new Illuminate\Support\HtmlString($sizes[$path] ? ' width="'.$sizes[$path][0].'" height="'.$sizes[$path][1].'"' : '');
    }
}

if (! function_exists('site_url')) {
    /**
     * Resolve a link from content. "our-products" or "gut-health.html" become
     * /{locale}/..., so content links follow the visitor's locale. A path that
     * already names a site language ("/en/faq.html") is switched to the
     * visitor's language; absolute URLs, anchors, mailto: and other paths
     * starting with "/" pass through.
     */
    function site_url(?string $link): string
    {
        $link = trim((string) $link);

        if ($link === '' || $link === '#') {
            return '#';
        }

        $locales = implode('|', array_map('preg_quote', (array) config('storefront.locales')));

        if (preg_match('#^/(?:'.$locales.')(?=/|$)/?(.*)$#', $link, $m)) {
            $link = $m[1] === '' ? 'home' : $m[1];
        }

        if (preg_match('#^([a-z][a-z0-9+.-]*:|//|/|\#|\?)#i', $link)) {
            return $link;
        }

        return url('/'.app('storefront.locale').'/'.$link);
    }
}

if (! function_exists('plain_text')) {
    /** Strip markup and decode entities (product names carry &reg;). */
    function plain_text(?string $html): string
    {
        return html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
