<?php

namespace App\Http\Controllers;

use App\Models\Product;

/**
 * Generated pack shot for products without an uploaded photo. It is drawn
 * from the product's own data (name, strength, dosage form, pack size), so it
 * follows renames and new products automatically.
 */
class PlaceholderController extends Controller
{
    public function pack(int $product)
    {
        $product = Product::withCatalogData()->findOrFail($product);

        $name = plain_text($product->name);

        // "Bioflor® Forte 500mg Sticks" → wordmark "BIOFLOR", line "Forte 500mg Sticks"
        $brand    = brand();
        $headline = trim(preg_replace('/^'.preg_quote($brand, '/').'\s*®?/iu', '', $name)) ?: $name;

        $svg = view('placeholders.pack', [
            'brand'    => mb_strtoupper($brand),
            'headline' => $headline,
            'strength' => $product->attrText('strength'),
            'form'     => $product->attrText('dosage_form'),
            'pack'     => $product->attrText('pack_size'),
            'strain'   => (string) setting('brand.strain', 'Saccharomyces boulardii CNCM I-745'),
            'accent'   => $this->safeColour((string) $product->attr('pack_color', '#001660')),
        ])->render();

        return response($svg, 200, [
            'Content-Type'  => 'image/svg+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    private function safeColour(string $value): string
    {
        return preg_match('/^#[0-9a-f]{3,8}$/i', $value) ? $value : '#001660';
    }
}
