<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Page;
use App\Models\PageSection;

/**
 * Resolves /{locale}/{slug}.html, in this order:
 *  1. a composed page — page_sections rows with page_key = slug (hubs, about us, ...)
 *  2. an editorial article
 *  3. a plain content page (legal texts)
 */
class PageController extends Controller
{
    public function show(string $locale, string $slug)
    {
        $page     = Page::where('slug', $slug)->first();
        $sections = PageSection::forPage($slug, null)->get();

        if ($sections->isNotEmpty()) {
            return view('pages.composed', [
                'page'     => $page,
                'pageKey'  => $slug,
                'sections' => $sections,
            ]);
        }

        $article = Article::where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->first();

        if ($article) {
            return view('pages.article', [
                'article' => $article,
                'related' => Article::published()
                    ->where('id', '!=', $article->id)
                    ->orderByRaw('category = ? desc', [$article->category])
                    ->take(6)
                    ->get(),
            ]);
        }

        // A composed page whose sections are all hidden is switched off, not empty.
        abort_unless($page && ! PageSection::where('page_key', $slug)->exists(), 404);

        return view('pages.content', ['page' => $page]);
    }

    /** /en/about-us → /en/about-us.html when that page exists. */
    public function legacy(string $locale, string $slug)
    {
        $aliases = ['terms-of-use' => 'terms'];
        $target  = $aliases[$slug] ?? $slug;

        $exists = PageSection::where('page_key', $target)->exists()
            || Page::where('slug', $target)->exists()
            || Article::where('slug', $target)->exists();

        abort_unless($exists, 404);

        return redirect()->route('page', ['slug' => $target], 301);
    }
}
