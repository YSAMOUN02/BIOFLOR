<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser security headers on every response. The Content Security Policy
 * only lets scripts load from this site, so a <script> or onerror="" pasted
 * into content (rich text, settings) does not run. Inline styles stay
 * allowed: content and Splide use them.
 */
class SecurityHeaders
{
    /** Hosts the pages load from: Google Fonts, and the video players the TV-spot section embeds. */
    private const POLICY = [
        'default-src'     => "'self'",
        'script-src'      => "'self'",
        'style-src'       => "'self' 'unsafe-inline' https://fonts.googleapis.com",
        'font-src'        => "'self' data: https://fonts.gstatic.com",
        'img-src'         => "'self' data: blob: https:",
        'media-src'       => "'self' blob: https:",
        'frame-src'       => 'https://www.youtube-nocookie.com https://player.vimeo.com',
        'connect-src'     => "'self'",
        'object-src'      => "'none'",
        'base-uri'        => "'self'",
        'form-action'     => "'self'",
        'frame-ancestors' => "'self'",
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $policy = self::POLICY;
        if ($request->isSecure()) {
            $policy['upgrade-insecure-requests'] = '';
        }

        $headers = [
            'Content-Security-Policy' => implode('; ', array_map(fn ($k, $v) => trim("$k $v"), array_keys($policy), $policy)),
            'X-Content-Type-Options'  => 'nosniff',
            'X-Frame-Options'         => 'SAMEORIGIN',
            'Referrer-Policy'         => 'strict-origin-when-cross-origin',
            'Permissions-Policy'      => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        // The admin and search results are not for search engines.
        if ($request->is('admin', 'admin/*') || $request->routeIs('search', 'search.suggest')) {
            $headers['X-Robots-Tag'] = 'noindex, nofollow';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');   // added by PHP itself (expose_php)
        }

        return $response;
    }
}
