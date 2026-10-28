<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Page;
use Illuminate\Http\Request;

/** "All news" listing: topic filter, sort, and a "show more" button. */
class ArticleController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request)
    {
        $tag  = is_string($request->query('tag')) ? $request->query('tag') : null;
        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'newest';
        $show = max(self::PER_PAGE, (int) $request->query('show', self::PER_PAGE));

        $query = Article::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($tag, fn ($q) => $q->where('eyebrow', $tag))
            ->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc');

        $total = (clone $query)->count();

        return view('pages.article-list', [
            'page'     => Page::where('slug', 'all-news')->first(),
            'articles' => $query->take($show)->get(),
            'total'    => $total,
            'show'     => $show,
            'perPage'  => self::PER_PAGE,
            'tags'     => Article::published()->whereNotNull('eyebrow')->distinct()->orderBy('eyebrow')->pluck('eyebrow'),
            'tag'      => $tag,
            'sort'     => $sort,
        ]);
    }
}
