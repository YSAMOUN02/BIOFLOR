<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\Retailer;

class ContentController extends Controller
{
    public function whereToBuy()
    {
        return view('pages.where-to-buy', [
            'page'      => Page::where('slug', 'find-in-store')->first(),
            'online'    => Retailer::where('is_online', true)->orderBy('name')->get(),
            'pharmacy'  => Retailer::where('is_online', false)->orderBy('country')->orderBy('city')->get(),
            'countries' => Retailer::orderBy('country')->distinct()->pluck('country'),
        ]);
    }

    /** Q&A page: composed from the 'faq' sections (header, numbered accordion, contact box). */
    public function faq()
    {
        return view('pages.composed', [
            'page'     => Page::where('slug', 'faq')->first(),
            'pageKey'  => 'faq',
            'sections' => PageSection::forPage('faq', null)->get(),
        ]);
    }

    public function contact()
    {
        return view('pages.contact', [
            'page'     => Page::where('slug', 'contact-us')->first(),
            'products' => Product::active()->get(['id', 'name']),
        ]);
    }

    public function sitemapPage()
    {
        return view('pages.sitemap', [
            'products' => Product::active()->get(),
            'articles' => Article::published()->get()->groupBy('category'),
            'pages'    => Page::orderBy('title')->get(),
        ]);
    }
}
