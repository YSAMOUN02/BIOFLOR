<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Locale;
use App\Models\NavItem;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Fallback so URL helpers work in console / before middleware runs.
        $this->app->instance('storefront.locale', config('storefront.default_locale'));
    }

    public function boot(): void
    {
        // Header, footer and newsletter data. Composed for the layout only;
        // partials included from it inherit these variables.
        View::composer('layouts.app', function ($view) {
            if (! Schema::hasTable('nav_items')) {
                return; // pre-migration safety
            }

            $articles = Article::published()->get(['id', 'title', 'slug', 'category', 'published_at']);

            $view->with([
                'globalNav'      => NavItem::roots()->with('children')->get(),
                'globalLocales'  => Locale::orderBy('sort_order')->get(),
                'footerProducts' => Product::active()->get(['id', 'name', 'slug']),
                'footerArticles' => $articles->groupBy('category'),
            ]);
        });
    }
}
