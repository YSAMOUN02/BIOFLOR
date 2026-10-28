<?php

namespace App\Http\Middleware;

use App\Models\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Resolves the /{locale}/... URL prefix (en, km, fr), sets the app language
 * (interface strings in lang/{prefix}.json, dates) and makes the prefix the
 * default for every generated route, so links stay inside the language.
 */
class SetStorefrontLocale
{
    public function handle(Request $request, Closure $next)
    {
        $prefix = $request->route('locale') ?? config('storefront.default_locale');

        $locale = Locale::where('url_prefix', $prefix)->first()
            ?? Locale::where('is_default', true)->first();

        if ($locale) {
            $prefix = $locale->url_prefix;
            app()->instance('storefront.locale_model', $locale);
        }

        app()->setLocale($prefix);
        Carbon::setLocale($prefix);
        app()->instance('storefront.locale', $prefix);
        URL::defaults(['locale' => $prefix]);

        return $next($request);
    }
}
