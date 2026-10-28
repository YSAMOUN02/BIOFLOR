<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PlaceholderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';

Route::redirect('/', '/'.config('storefront.default_locale').'/home');

Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap.xml');

// Generated pack shot used until a real product photo is uploaded.
Route::get('placeholder/pack/{product}.svg', [PlaceholderController::class, 'pack'])
    ->whereNumber('product')
    ->name('placeholder.pack');

Route::prefix('{locale}')
    ->whereIn('locale', config('storefront.locales'))
    ->group(function () {

        Route::get('home', HomeController::class)->name('home');

        Route::get('search', [SearchController::class, 'index'])->name('search');
        Route::get('search/suggest', [SearchController::class, 'suggest'])->middleware('throttle:60,1')->name('search.suggest');

        Route::get('our-products', [ProductController::class, 'index'])->name('products');
        Route::get('all-news.html', [ArticleController::class, 'index'])->name('articles');
        Route::get('find-in-store', [ContentController::class, 'whereToBuy'])->name('where-to-buy');
        Route::get('faq.html', [ContentController::class, 'faq'])->name('faq');
        Route::get('contact-us.html', [ContentController::class, 'contact'])->name('contact');
        Route::get('sitemap', [ContentController::class, 'sitemapPage'])->name('sitemap');

        // A visitor sends a few messages at most; the limit stops scripted spam.
        Route::post('newsletter', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter.subscribe');
        Route::post('contact-us.html', [NewsletterController::class, 'contact'])->middleware('throttle:5,1')->name('contact.send');

        // Paths used by the previous build.
        foreach (['products' => 'products', 'where-to-buy' => 'where-to-buy', 'q-and-a' => 'faq', 'contact-us' => 'contact'] as $old => $name) {
            Route::get($old, fn () => redirect()->route($name, status: 301));
        }

        // Product detail: /en/bioflor-250mg-sachets/11111.html
        Route::get('{slug}/{id}.html', [ProductController::class, 'show'])
            ->whereNumber('id')
            ->name('product');

        // Composed pages, editorial articles and legal pages: /en/gut-health.html
        Route::get('{slug}.html', [PageController::class, 'show'])->name('page');

        // Extension-less legacy links (/en/about-us) → their .html address.
        Route::get('{slug}', [PageController::class, 'legacy'])->name('page.legacy');
    });
