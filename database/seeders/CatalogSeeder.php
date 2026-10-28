<?php

namespace Database\Seeders;

use App\Models\AttributeGroup;
use App\Models\AttributeSet;
use App\Models\Category;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;

/**
 * Attribute dictionary, product types and category tree. Everything is keyed
 * by `code`, so the seeder is safe to re-run and the same definitions can be
 * reused for another range: add attributes here (or in the admin) instead of
 * adding columns to `products`.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $groups = $this->groups();
        $this->attributes($groups);
        $this->sets();
        $this->categories();
    }

    /** @return array<string, int> group code => id */
    private function groups(): array
    {
        $rows = [
            'labels'    => ['Labels', 'labels', 10],
            'badges'    => ['Badges', 'badges', 20],
            'specs'     => ['Key facts', 'specs', 30],
            'accordion' => ['Product information', 'accordion', 40],
            'steps'     => ['How to use', 'steps', 50],
            'hidden'    => ['Internal', 'hidden', 90],
        ];

        foreach ($rows as $code => [$name, $display, $sort]) {
            AttributeGroup::updateOrCreate(['code' => $code], ['name' => $name, 'display' => $display, 'sort_order' => $sort]);
        }

        return AttributeGroup::pluck('id', 'code')->all();
    }

    private function attributes(array $groups): void
    {
        // code => [name, type, group, extra]
        $rows = [
            // labels: "Drug / 20 sachets / Adults & children"
            'legal_status' => ['Legal status', 'select', 'labels', ['options' => [
                'drug' => 'Drug', 'supplement' => 'Food supplement', 'device' => 'Medical device',
            ], 'is_filterable' => true, 'is_required' => true]],
            'pack_size'    => ['Pack size', 'text', 'labels', ['help' => 'As printed on the pack, e.g. "20 sachets".']],
            'age_range'    => ['Suitable for', 'text', 'labels', ['help' => 'Approved age wording, e.g. "Adults & children".']],

            // badges (yes/no + icon)
            'gmo_free'     => ['No GMO', 'boolean', 'badges', ['icon' => 'gmo-free']],
            'gluten_free'  => ['Gluten free', 'boolean', 'badges', ['icon' => 'gluten-free']],
            'vegetarian'   => ['Vegetarian', 'boolean', 'badges', ['icon' => 'vegetarian']],
            'lactose_free' => ['Lactose free', 'boolean', 'badges', ['icon' => 'lactose-free']],

            // key facts
            'dosage_form'       => ['Dosage form', 'select', 'specs', ['options' => [
                'sachet' => 'Sachet', 'capsule' => 'Capsule', 'stick' => 'Stick', 'vial' => 'Vial', 'drops' => 'Drops',
            ], 'is_filterable' => true]],
            'strength'          => ['Strength', 'number', 'specs', ['unit' => 'mg']],
            'active_ingredient' => ['Active ingredient', 'text', 'specs', ['is_searchable' => true]],
            'units_per_pack'    => ['Units per pack', 'number', 'specs', []],

            // accordion panels, in this order
            'usage'       => ['Indications', 'list', 'accordion', ['help' => 'One indication per line (approved wording).', 'is_searchable' => true]],
            'dosage'      => ['Dosage', 'richtext', 'accordion', []],
            'precautions' => ['Precautions for use', 'richtext', 'accordion', []],
            'storage'     => ['Storage', 'richtext', 'accordion', []],
            'ingredients' => ['Ingredients', 'richtext', 'accordion', []],

            // numbered steps
            'how_to_use' => ['How to use', 'list', 'steps', ['help' => 'One step per line. Optional icon prefix: [pour] [mix] [drink] [swallow] [shake] [drops].']],

            // not displayed directly
            'tagline'             => ['Tagline', 'text', 'hidden', []],
            'badge'               => ['Tile badge', 'text', 'hidden', ['help' => 'Short ribbon on the product tile, e.g. "New".']],
            'description'         => ['Long description', 'richtext', 'hidden', []],
            'how_it_works'        => ['How it works (text)', 'richtext', 'hidden', ['help' => 'Overrides the shared "How it works" text on this product page.']],
            'references'          => ['References', 'richtext', 'hidden', ['help' => 'Numbered references shown under "See all references".']],
            'registration_number' => ['Registration number', 'text', 'hidden', ['is_searchable' => true]],
            'gtin'                => ['Barcode (GTIN)', 'text', 'hidden', []],
            'pack_color'          => ['Pack colour', 'color', 'hidden', ['help' => 'Accent of the generated pack shot.']],
        ];

        $sort = 0;

        foreach ($rows as $code => [$name, $type, $group, $extra]) {
            ProductAttribute::updateOrCreate(['code' => $code], [
                'name'               => $name,
                'type'               => $type,
                'attribute_group_id' => $groups[$group],
                'unit'               => $extra['unit'] ?? null,
                'options'            => $extra['options'] ?? null,
                'icon'               => $extra['icon'] ?? null,
                'help'               => $extra['help'] ?? null,
                'is_required'        => $extra['is_required'] ?? false,
                'is_filterable'      => $extra['is_filterable'] ?? false,
                'is_searchable'      => $extra['is_searchable'] ?? false,
                'sort_order'         => $sort += 10,
            ]);
        }
    }

    private function sets(): void
    {
        $all = ProductAttribute::orderBy('sort_order')->pluck('id', 'code');

        $sets = [
            'probiotic-medicine' => ['Probiotic medicine', 'Over-the-counter medicine: full product information, dosage and precautions.', $all->keys()->all()],
            'food-supplement'    => ['Food supplement', 'Supplement range: no registration number, otherwise the same page layout.', $all->keys()->reject(fn ($c) => in_array($c, ['registration_number', 'precautions'], true))->all()],
        ];

        foreach ($sets as $code => [$name, $description, $codes]) {
            $set = AttributeSet::updateOrCreate(['code' => $code], ['name' => $name, 'description' => $description]);

            $set->productAttributes()->sync(collect($codes)->values()->mapWithKeys(
                fn ($c, $i) => [$all[$c] => ['sort_order' => ($i + 1) * 10]]
            )->all());
        }
    }

    private function categories(): void
    {
        $root = Category::updateOrCreate(['code' => 'our-products'], [
            'slug' => 'our-products', 'name' => 'Our products', 'sort_order' => 1, 'is_active' => true,
            'description' => 'The full probiotic yeast range.',
        ]);

        foreach (['capsules' => 'Capsules', 'sachets' => 'Sachets', 'sticks' => 'Sticks', 'vials' => 'Vials', 'drops' => 'Drops'] as $i => $name) {
            Category::updateOrCreate(['code' => $i], [
                'parent_id' => $root->id, 'slug' => $i, 'name' => $name, 'is_active' => true,
                'sort_order' => array_search($i, ['capsules', 'sachets', 'sticks', 'vials', 'drops'], true) + 1,
            ]);
        }
    }
}
