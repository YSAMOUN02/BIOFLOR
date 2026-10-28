<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Locale;
use App\Models\PageSection;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * robots.txt and sitemap.xml for search engines. Built from the database,
 * so new products, articles and pages are listed without any extra step.
 */
class SeoController extends Controller
{
    /** Pages with their own route, listed under their address. */
    private const FIXED = ['home', 'our-products', 'find-in-store', 'faq.html', 'contact-us.html', 'all-news.html'];

    /** Composed page keys that are not pages of their own. */
    private const NOT_PAGES = ['home', 'pdp', 'faq', 'our-products'];

    /** Content pages linked from the site (terms, cookies and accessibility were unlinked at the client's request). */
    private const CONTENT_PAGES = ['data-protection'];

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /*/search',
            '',
            'Sitemap: '.url('sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function sitemap()
    {
        $locales = array_values(array_diff((array) config('storefront.locales'), (array) config('storefront.noindex_locales', [])));

        $xml = view('seo.sitemap', [
            'entries' => $this->entries(),
            'locales' => $locales,
            'codes'   => Locale::pluck('code', 'url_prefix'),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /** @return Collection<int, array{path: string, lastmod: ?string}> paths without the language prefix */
    private function entries(): Collection
    {
        $pages = PageSection::distinct()->pluck('page_key')
            ->diff(self::NOT_PAGES)
            ->merge(self::CONTENT_PAGES)
            ->map(fn ($key) => ['path' => $key.'.html', 'lastmod' => null]);

        $products = Product::active()->orderBy('sort_order')->get(['id', 'slug', 'updated_at'])
            ->map(fn ($p) => ['path' => $p->slug.'/'.$p->id.'.html', 'lastmod' => $p->updated_at?->toDateString()]);

        $articles = Article::published()->get(['slug', 'updated_at', 'published_at'])
            ->map(fn ($a) => ['path' => $a->slug.'.html', 'lastmod' => ($a->updated_at ?? $a->published_at)?->toDateString()]);

        return collect(self::FIXED)
            ->map(fn ($path) => ['path' => $path, 'lastmod' => null])
            ->concat($pages)->concat($products)->concat($articles)
            ->unique('path')->values();
    }
}
