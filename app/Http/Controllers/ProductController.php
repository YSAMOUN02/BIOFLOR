<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;

class ProductController extends Controller
{
    /** Range page: composed from the 'our-products' sections, with a plain grid as fallback. */
    public function index()
    {
        $sections = PageSection::forPage('our-products', null)->get();

        if ($sections->isNotEmpty()) {
            return view('pages.composed', [
                'page'     => Page::where('slug', 'our-products')->first(),
                'pageKey'  => 'our-products',
                'sections' => $sections,
            ]);
        }

        return view('pages.product-list', [
            'products' => Product::active()->withCatalogData()->get(),
        ]);
    }

    public function show(string $locale, string $slug, int $id)
    {
        $product = Product::withCatalogData()->with(['faqs', 'reviews'])->findOrFail($id);

        abort_unless($product->is_active, 404);

        // Canonicalise: the slug in the URL must match the product's own slug.
        if ($product->slug !== $slug) {
            return redirect()->to($product->url, 301);
        }

        // Shared product-page sections, grouped by the region they render in.
        $sections = PageSection::forPage('pdp', null)->get()
            ->each->withTokens(['{product}' => $product->name])
            ->groupBy('region');

        return view('pages.product', [
            'product'  => $product,
            'sections' => $sections,
        ]);
    }
}
