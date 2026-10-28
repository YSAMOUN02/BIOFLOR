<?php

namespace Database\Seeders;

use App\Models\NavItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Main menu: "Our products" opens a dropdown of the range; the other entries
 * go straight to their hub pages. Footer columns are built from products and
 * articles, not from this table.
 */
class NavSeeder extends Seeder
{
    public function run(): void
    {
        NavItem::query()->delete();

        // Stored without the language prefix: site_url() opens each link in the visitor's language.
        $url = fn (string $path) => ltrim($path, '/');

        $products = NavItem::create([
            'label'       => 'Our products',
            'url'         => $url('our-products'),
            'description' => 'Discover the {brand} probiotic yeast range.',
            'cta_label'   => 'Discover our range',
            'sort_order'  => 1,
        ]);

        foreach (Product::active()->get() as $i => $product) {
            NavItem::create([
                'parent_id'  => $products->id,
                'label'      => $product->plain_name,
                'url'        => $url($product->slug.'/'.$product->id.'.html'),
                'sort_order' => $i + 1,
            ]);
        }

        foreach ([
            ['Gut health', 'gut-health.html'],
            ['Our uniqueness', 'our-uniqueness.html'],
            ['Our news', 'our-news.html'],
            ['About us', 'about-us.html'],
        ] as $i => [$label, $path]) {
            NavItem::create(['label' => $label, 'url' => $url($path), 'sort_order' => $i + 2]);
        }
    }
}
