<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = $this->query($request);

        $products = $q === '' ? collect() : $this->products($q)->withCatalogData()->get();
        $articles = $q === '' ? collect() : $this->articles($q)->take(24)->get();

        return view('pages.search', compact('q', 'products', 'articles'));
    }

    /** Type-ahead used by the header search field. */
    public function suggest(Request $request)
    {
        $q = $this->query($request);

        if (mb_strlen($q) < 2) {
            return response()->json(['products' => [], 'articles' => []]);
        }

        return response()->json([
            'products' => $this->products($q)->take(4)->get()
                ->map(fn ($p) => ['label' => $p->plain_name, 'url' => $p->url, 'image' => $p->image_url]),
            'articles' => $this->articles($q)->take(4)->get()
                ->map(fn ($a) => ['label' => plain_text($a->title), 'url' => $a->url]),
        ]);
    }

    /** The search words: text only (q[]=… is ignored), at most 100 characters. */
    private function query(Request $request): string
    {
        $q = $request->query('q');

        return is_string($q) ? mb_substr(trim($q), 0, 100) : '';
    }

    /** Name, intro, and any searchable attribute value. */
    private function products(string $q)
    {
        $like = '%'.addcslashes($q, '%_').'%';

        return Product::active()->where(fn ($w) => $w
            ->where('name', 'like', $like)
            ->orWhere('short_description', 'like', $like)
            ->orWhereHas('translations', fn ($t) => $t->where('value', 'like', $like))
            ->orWhereHas('attributeValues', fn ($v) => $v
                ->where('value', 'like', $like)
                ->whereHas('definition', fn ($d) => $d->where('is_searchable', true))));
    }

    private function articles(string $q)
    {
        $like = '%'.addcslashes($q, '%_').'%';

        return Article::published()->where(fn ($w) => $w
            ->where('title', 'like', $like)
            ->orWhere('excerpt', 'like', $like)
            ->orWhere('body', 'like', $like)
            ->orWhereHas('translations', fn ($t) => $t->where('value', 'like', $like)));
    }
}
