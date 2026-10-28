<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductFaq;
use App\Models\Review;
use App\Support\CatalogImporter;
use Illuminate\Database\Seeder;

/**
 * The product range comes from the Product folder (product list + pack shots),
 * imported with CatalogImporter — the same code as `php artisan catalog:import`.
 * Without that folder, the two products of the list are created from the
 * fallback below.
 *
 * Then every product gets placeholder content where it has none yet: intro,
 * usage, dosage, storage, how-to-use steps, Q&A and sample
 * reviews. Product information for a medicine must be the regulator-approved
 * wording for your market — replace it in the admin before publishing.
 * Existing values are never overwritten, so admin edits survive a re-seed.
 */
class ProductSeeder extends Seeder
{
    private const PLACEHOLDER = '<p><em>Placeholder — replace with the approved wording for your market.</em></p>';

    public function run(CatalogImporter $importer): void
    {
        $folder = base_path('Product');

        $products = is_dir($folder) ? $importer->import($folder) : $this->fallback();

        foreach ($importer->log as $line) {
            $this->command?->line($line);
        }

        // Only the imported range stays; the template's other products are removed.
        Product::whereNotIn('id', $products->pluck('id'))->get()->each->delete();

        foreach ($products as $product) {
            $this->enrich($product->fresh());
        }
    }

    /** The product list, for installs without the Product folder. */
    private function fallback()
    {
        $brand = brand();
        $rows  = [
            [11111, 'sachet', 'Sachets', '10 sachets'],
            [11112, 'capsule', 'Capsules', '10 capsules'],
        ];

        return collect($rows)->map(function ($row, $i) use ($brand) {
            [$id, $form, $label, $pack] = $row;

            $product = Product::updateOrCreate(['id' => $id], [
                'name'       => $brand.'&reg; 250mg '.$label,
                'slug'       => strtolower($brand).'-250mg-'.strtolower($label),
                'sort_order' => $i + 1,
                'is_active'  => true,
            ]);

            foreach (['dosage_form' => $form, 'strength' => 250, 'units_per_pack' => 10, 'pack_size' => $pack] as $code => $value) {
                $this->fill($product, $code, $value);
            }

            return $product;
        });
    }

    private function enrich(Product $product): void
    {
        $form = (string) $product->attr('dosage_form');
        $name = $product->plain_name;

        if (! $product->short_description) {
            $product->update([
                'short_description' => $form === 'capsule'
                    ? 'Probiotic yeast capsules, easy to take at home or on the go.'
                    : 'Probiotic yeast in single-dose sachets of powder, ready to mix into a little water.',
                'meta_description' => $name.' — '.(string) $product->attr('active_ingredient', 'probiotic yeast').'.',
            ]);
        }

        $defaults = [
            'active_ingredient' => (string) setting('brand.strain', 'Saccharomyces boulardii CNCM I-745'),
            'usage'             => "Indication 1 (placeholder — approved wording)\nIndication 2 (placeholder — approved wording)",
            'dosage'            => '<p>Follow the dosage on the pack or the advice of your doctor or pharmacist.</p>'.self::PLACEHOLDER,
            'storage'           => '<p>Keep in the original packaging, out of the sight and reach of children.</p>'.self::PLACEHOLDER,
            'ingredients'       => '<p>Active ingredient and excipients as listed in the package leaflet.</p>'.self::PLACEHOLDER,
            // From the approved dosage and Q&A (Bioflor product list, 2026).
            'how_to_use'        => $form === 'capsule'
                ? "[swallow] Swallow the capsule with water.\n[drink] Take 1 or 2 capsules, once or twice daily."
                : "[pour] Open the sachet.\n[mix] Mix the powder with water or food.\n[drink] Take 1 or 2 sachets, once or twice daily.",
            'references'        => '<p>(1) Add the approved reference for each claim on this page.</p>',
        ];

        foreach ($defaults as $code => $value) {
            $this->fill($product, $code, $value);
        }

        if ($product->faqs()->doesntExist()) {
            $answer = 'Ask your doctor or pharmacist for advice. <em>Placeholder — use the approved answer for your market.</em>';

            foreach ([
                ["How should I take {$name}?", 'Follow the dosage and preparation on the pack. '.$answer],
                ["Can I take {$name} with antibiotics?", $answer],
                ["Is {$name} suitable for children?", 'Check the age range on the pack. '.$answer],
                ['What should I do if symptoms continue?', 'If symptoms persist or get worse, see a doctor. '.$answer],
                ['How should I store it?', 'Keep it in its original packaging, out of the sight and reach of children.'],
            ] as $i => [$question, $text]) {
                ProductFaq::create(['product_id' => $product->id, 'question' => $question, 'answer' => $text, 'sort_order' => $i + 1]);
            }
        }

        if ($product->reviews()->doesntExist()) {
            foreach ([
                ['Sample reviewer A', 5, 'Easy to take'],
                ['Sample reviewer B', 4, 'Handy format'],
            ] as [$author, $rating, $title]) {
                Review::create([
                    'product_id'  => $product->id,
                    'author'      => $author,
                    'rating'      => $rating,
                    'title'       => $title,
                    'body'        => 'Sample review — real reviews will come from your customers or ratings provider.',
                    'is_approved' => true,
                ]);
            }
        }
    }

    /** Set an attribute only when the product has no value for it yet. */
    private function fill(Product $product, string $code, mixed $value): void
    {
        $definition = ProductAttribute::where('code', $code)->first();

        if (! $definition) {
            return;
        }

        $exists = ProductAttributeValue::where('product_id', $product->id)
            ->where('attribute_id', $definition->id)
            ->where('locale', '')
            ->whereNotNull('value')
            ->exists();

        if (! $exists) {
            ProductAttributeValue::updateOrCreate(
                ['product_id' => $product->id, 'attribute_id' => $definition->id, 'locale' => ''],
                ['value' => $definition->serialize($value)],
            );
        }
    }
}
