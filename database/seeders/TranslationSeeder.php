<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Locale;
use App\Models\NavItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductFaq;
use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Database\Seeder;

/**
 * Khmer (km) versions of the site's text. These are working translations of
 * the placeholder copy: have a native speaker review them, and replace
 * medical wording with the approved Khmer text for the market. Article and
 * legal-page bodies are not translated here and show in English until a
 * Khmer version is entered in the admin.
 *
 * Run after the content seeders (it matches records by slug, label or order).
 */
class TranslationSeeder extends Seeder
{
    private const L = 'km';

    private const FORMS = ['sachet' => 'ថង់', 'capsule' => 'កាបស៊ុល', 'stick' => 'ស្ទីក', 'vial' => 'ដប', 'drops' => 'ដំណក់', 'tablet' => 'គ្រាប់'];

    /**
     * English sentence (as stored) → Khmer. Product information and Q&A
     * from the approved Bioflor product list (2026), plus the placeholders
     * ProductSeeder writes when that list is missing.
     */
    private const TEXT = [
        // indications
        'Treatment of acute infectious diarrhoea of adults and children.' => 'ការព្យាបាលរាគរូសស្រួចស្រាវដោយសារការឆ្លងមេរោគ ចំពោះមនុស្សពេញវ័យ និងកុមារ។',
        'Prevention and treatment of antibiotic-associated colitis and diarrhoea.' => 'ការការពារ និងការព្យាបាលការរលាកពោះវៀនធំ និងរាគរូស ដែលទាក់ទងនឹងការប្រើថ្នាំអង់ទីប៊ីយោទិក។',
        'Addition to vancomycin/metronidazole treatment to prevent recurrence of Clostridium difficile diseases.' => 'ជាការព្យាបាលបន្ថែមលើថ្នាំ vancomycin/metronidazole ដើម្បីការពារការកើតឡើងវិញនៃជំងឺដែលបង្កដោយ Clostridium difficile។',
        'Prevention of tube-feeding associated diarrhoea.' => 'ការការពាររាគរូសដែលទាក់ទងនឹងការផ្តល់អាហារតាមបំពង់។',
        'Treatment of irritable bowel syndrome.' => 'ការព្យាបាលរោគសញ្ញាពោះវៀនរំខាន (IBS)។',
        // dosage, storage, ingredients
        'Oral route, 1 or 2 sachets, given once or twice daily.' => 'ប្រើតាមមាត់៖ ១ ឬ ២ ថង់ ម្តង ឬពីរដងក្នុងមួយថ្ងៃ។',
        'Oral route, 1 or 2 capsules, given once or twice daily.' => 'ប្រើតាមមាត់៖ ១ ឬ ២ កាបស៊ុល ម្តង ឬពីរដងក្នុងមួយថ្ងៃ។',
        'Store away from reach and sight of children' => 'ទុកឱ្យឆ្ងាយពីការមើលឃើញ និងការចាប់កាន់របស់កុមារ។',
        'Store below 30°C.' => 'រក្សាទុកនៅសីតុណ្ហភាពក្រោម 30°C។',
        'Saccharomyces boulardii CNCM I-745 — 250 mg per sachet' => 'Saccharomyces boulardii CNCM I-745 — 250 mg ក្នុងមួយថង់',
        'Saccharomyces boulardii CNCM I-745 — 250 mg per capsule' => 'Saccharomyces boulardii CNCM I-745 — 250 mg ក្នុងមួយកាបស៊ុល',
        // how to use (steps)
        'Open the sachet.' => 'បើកថង់។',
        'Mix the powder with water or food.' => 'លាយម្សៅជាមួយទឹក ឬអាហារ។',
        'Take 1 or 2 sachets, once or twice daily.' => 'ប្រើ ១ ឬ ២ ថង់ ម្តង ឬពីរដងក្នុងមួយថ្ងៃ។',
        'Swallow the capsule with water.' => 'លេបកាបស៊ុលជាមួយទឹក។',
        'Take 1 or 2 capsules, once or twice daily.' => 'ប្រើ ១ ឬ ២ កាបស៊ុល ម្តង ឬពីរដងក្នុងមួយថ្ងៃ។',
        // product Q&A — sachets
        'How should I take Bioflor®?' => 'តើត្រូវប្រើ Bioflor® ដោយរបៀបណា?',
        'Mix the sachet with water or food. Follow the recommended dosage.' => 'លាយម្សៅក្នុងថង់ជាមួយទឹក ឬអាហារ។ សូមប្រើតាមកម្រិតដែលបានណែនាំ។',
        'Can I take Bioflor® with antibiotics?' => 'តើខ្ញុំអាចប្រើ Bioflor® ជាមួយថ្នាំអង់ទីប៊ីយោទិកបានទេ?',
        'Yes. Bioflor® contains a probiotic yeast that can be taken with most antibiotics.' => 'បាន។ Bioflor® មានផ្ទុកមេដំបែប្រូបាយអូទិក ដែលអាចប្រើជាមួយថ្នាំអង់ទីប៊ីយោទិកភាគច្រើនបាន។',
        'Is Bioflor® suitable for children?' => 'តើ Bioflor® សមស្របសម្រាប់កុមារដែរឬទេ?',
        'Yes, for eligible age groups. Consult a doctor or pharmacist for appropriate use and dosage.' => 'បាន សម្រាប់ក្រុមអាយុដែលសមស្រប។ សូមពិគ្រោះជាមួយគ្រូពេទ្យ ឬឱសថការី អំពីការប្រើប្រាស់ និងកម្រិតប្រើដែលត្រឹមត្រូវ។',
        'What if diarrhea continues?' => 'ចុះប្រសិនបើរាគរូសនៅតែបន្ត?',
        'Consult a doctor if symptoms persist or worsen. Maintain adequate hydration.' => 'សូមពិគ្រោះជាមួយគ្រូពេទ្យ ប្រសិនបើរោគសញ្ញានៅតែមាន ឬកាន់តែធ្ងន់ធ្ងរ។ សូមផឹកទឹកឱ្យបានគ្រប់គ្រាន់។',
        'How should I store Bioflor®?' => 'តើត្រូវរក្សាទុក Bioflor® ដោយរបៀបណា?',
        'Store in a cool, dry place according to the packaging instructions.' => 'រក្សាទុកនៅកន្លែងត្រជាក់ និងស្ងួត តាមការណែនាំនៅលើប្រអប់។',
        'Who should avoid Bioflor®?' => 'តើអ្នកណាខ្លះមិនគួរប្រើ Bioflor®?',
        'Patients with central venous catheters, critical illness, or compromised immunity should not use it.' => 'អ្នកជំងឺដែលមានបំពង់បញ្ចូលតាមសរសៃវ៉ែនកណ្តាល អ្នកជំងឺធ្ងន់ធ្ងរ ឬអ្នកដែលមានប្រព័ន្ធភាពស៊ាំចុះខ្សោយ មិនគួរប្រើវាឡើយ។',
        // product Q&A — capsules
        'How should I take Bioflor® 250 mg Capsules?' => 'តើត្រូវប្រើ Bioflor® 250 mg កាបស៊ុល ដោយរបៀបណា?',
        'Swallow the capsule with water. Follow the recommended dosage or your healthcare professional’s advice.' => 'លេបកាបស៊ុលជាមួយទឹក។ សូមប្រើតាមកម្រិតដែលបានណែនាំ ឬតាមដំបូន្មានរបស់បុគ្គលិកសុខាភិបាលរបស់អ្នក។',
        'Yes. Bioflor® contains Saccharomyces boulardii CNCM I-745, a probiotic yeast that can be taken with most antibiotics.' => 'បាន។ Bioflor® មានផ្ទុក Saccharomyces boulardii CNCM I-745 ដែលជាមេដំបែប្រូបាយអូទិក អាចប្រើជាមួយថ្នាំអង់ទីប៊ីយោទិកភាគច្រើនបាន។',
        'Is Bioflor® 250 mg suitable for children?' => 'តើ Bioflor® 250 mg សមស្របសម្រាប់កុមារដែរឬទេ?',
        'It may be suitable for children, depending on their age and ability to swallow capsules. Consult a doctor or pharmacist.' => 'វាអាចសមស្របសម្រាប់កុមារ អាស្រ័យលើអាយុ និងសមត្ថភាពក្នុងការលេបកាបស៊ុល។ សូមពិគ្រោះជាមួយគ្រូពេទ្យ ឬឱសថការី។',
        'What should I do if symptoms continue?' => 'តើខ្ញុំគួរធ្វើដូចម្តេច ប្រសិនបើរោគសញ្ញានៅតែបន្ត?',
        'Consult a doctor if diarrhea persists, worsens, or is accompanied by fever, blood in the stool, or dehydration.' => 'សូមពិគ្រោះជាមួយគ្រូពេទ្យ ប្រសិនបើរាគរូសនៅតែបន្ត កាន់តែធ្ងន់ធ្ងរ ឬមានគ្រុនក្តៅ មានឈាមក្នុងលាមក ឬខ្សោះជាតិទឹក។',
        'Store in a cool, dry place according to the packaging instructions. Keep out of reach of children' => 'រក្សាទុកនៅកន្លែងត្រជាក់ និងស្ងួត តាមការណែនាំនៅលើប្រអប់។ ទុកឱ្យឆ្ងាយពីការចាប់កាន់របស់កុមារ។',
        // placeholders written by ProductSeeder (still to be replaced)
        'Indication 1 (placeholder — approved wording)' => 'ការចង្អុលបង្ហាញទី ១ (កន្លែងសម្រាប់ពាក្យដែលបានអនុម័ត)',
        'Indication 2 (placeholder — approved wording)' => 'ការចង្អុលបង្ហាញទី ២ (កន្លែងសម្រាប់ពាក្យដែលបានអនុម័ត)',
        'Follow the dosage on the pack or the advice of your doctor or pharmacist.' => 'សូមប្រើតាមកម្រិតដែលបានណែនាំនៅលើប្រអប់ ឬតាមដំបូន្មានរបស់គ្រូពេទ្យ ឬឱសថការីរបស់អ្នក។',
        'Read the package leaflet before use. Ask a healthcare professional if symptoms persist.' => 'សូមអានសន្លឹកណែនាំមុនពេលប្រើ។ សូមពិគ្រោះជាមួយបុគ្គលិកសុខាភិបាល ប្រសិនបើរោគសញ្ញានៅតែមាន។',
        'Keep in the original packaging, out of the sight and reach of children.' => 'រក្សាទុកក្នុងប្រអប់ដើម ឱ្យឆ្ងាយពីការមើលឃើញ និងការចាប់កាន់របស់កុមារ។',
        'Active ingredient and excipients as listed in the package leaflet.' => 'សារធាតុសកម្ម និងសារធាតុជំនួយ ដូចមានក្នុងសន្លឹកណែនាំ។',
        '<em>Placeholder — replace with the approved wording for your market.</em>' => '<em>អត្ថបទគំរូ — សូមជំនួសដោយពាក្យដែលបានអនុម័តសម្រាប់ទីផ្សាររបស់អ្នក។</em>',
        '(1) Add the approved reference for each claim on this page.' => '(1) សូមបន្ថែមឯកសារយោងដែលបានអនុម័ត សម្រាប់ការអះអាងនីមួយៗនៅលើទំព័រនេះ។',
    ];

    public function run(): void
    {
        $locale = Locale::where('url_prefix', self::L)->first();

        if (! $locale) {
            return;
        }

        $this->removeOrphans();
        $this->categories();
        $this->attributes();
        $this->products($locale->code);
        $this->nav();
        $this->articles();
        $this->pages();
        $this->faqs();
        $this->settings();
        $this->sections();
    }

    /** Rows whose record no longer exists (menus and sections are rebuilt by their seeders). */
    private function removeOrphans(): void
    {
        foreach (Translation::select('translatable_type')->distinct()->pluck('translatable_type') as $type) {
            if (class_exists($type)) {
                Translation::where('translatable_type', $type)->whereNotIn('translatable_id', $type::query()->pluck((new $type)->getKeyName()))->delete();
            }
        }
    }

    private function categories(): void
    {
        $rows = [
            'our-products' => ['ផលិតផលរបស់យើង', 'ផលិតផលមេដំបែប្រូបាយអូទិកទាំងអស់។'],
            'capsules'     => ['កាបស៊ុល', null],
            'sachets'      => ['ថង់', null],
            'sticks'       => ['ស្ទីក', null],
            'vials'        => ['ដប', null],
            'drops'        => ['ដំណក់', null],
        ];

        foreach ($rows as $code => [$name, $description]) {
            Category::where('code', $code)->first()?->setTranslations(self::L, ['name' => $name, 'description' => $description]);
        }
    }

    private function attributes(): void
    {
        $rows = [
            'legal_status'      => ['ស្ថានភាពច្បាប់', ['drug' => 'ឱសថ', 'supplement' => 'អាហារបំប៉ន', 'device' => 'ឧបករណ៍វេជ្ជសាស្ត្រ']],
            'pack_size'         => ['ទំហំកញ្ចប់', null],
            'age_range'         => ['សមស្របសម្រាប់', null],
            'gmo_free'          => ['គ្មាន GMO', null],
            'gluten_free'       => ['គ្មានគ្លុយតែន', null],
            'vegetarian'        => ['សម្រាប់អ្នកបួស', null],
            'lactose_free'      => ['គ្មានឡាក់តូស', null],
            'dosage_form'       => ['ទម្រង់ឱសថ', self::FORMS],
            'strength'          => ['កម្រិតជាតិឱសថ', null],
            'active_ingredient' => ['សារធាតុសកម្ម', null],
            'units_per_pack'    => ['ចំនួនក្នុងមួយកញ្ចប់', null],
            'usage'             => ['ការចង្អុលបង្ហាញ', null],
            'dosage'            => ['កម្រិតប្រើ', null],
            'precautions'       => ['ការប្រុងប្រយ័ត្នក្នុងការប្រើប្រាស់', null],
            'storage'           => ['ការរក្សាទុក', null],
            'ingredients'       => ['គ្រឿងផ្សំ', null],
            'how_to_use'        => ['របៀបប្រើប្រាស់', null],
        ];

        foreach ($rows as $code => [$name, $options]) {
            $attribute = ProductAttribute::where('code', $code)->first();
            if (! $attribute) {
                continue;
            }

            $fields = ['name' => $name];
            if ($options) {
                // Keep every option the attribute has; translate the known ones.
                $current = json_decode((string) $attribute->getRawOriginal('options'), true) ?: [];
                $fields['options'] = array_intersect_key($options, $current) + $current;
            }

            $attribute->setTranslations(self::L, $fields);
        }
    }

    private function products(string $localeCode): void
    {
        $definitions = ProductAttribute::all()->keyBy('code');

        foreach (Product::withCatalogData()->get() as $product) {
            $form    = (string) $product->attr('dosage_form');
            $units    = $product->attr('units_per_pack');
            $strength = $product->attr('strength');
            $word     = self::FORMS[$form] ?? '';
            $name     = trim(brand().'&reg; '.($strength ? rtrim(rtrim(number_format((float) $strength, 2, '.', ''), '0'), '.').'mg ' : '').$word);
            $plain    = plain_text($name);

            $product->setTranslations(self::L, [
                'name'              => $name,
                'short_description' => $form === 'capsule'
                    ? 'កាបស៊ុលមេដំបែប្រូបាយអូទិក ងាយស្រួលប្រើនៅផ្ទះ ឬពេលធ្វើដំណើរ។'
                    : 'មេដំបែប្រូបាយអូទិកក្នុងថង់ម្សៅមួយដូស ងាយស្រួលលាយជាមួយទឹកបន្តិច។',
                'meta_description'  => $plain.' — Saccharomyces boulardii CNCM I-745។',
            ]);

            // Language-specific attribute values, translated sentence by sentence
            // from the English now stored (approved product list or placeholders).
            $english = ProductAttributeValue::where('product_id', $product->id)->where('locale', '')->get()->keyBy('attribute_id');

            foreach (['usage', 'dosage', 'precautions', 'storage', 'ingredients', 'how_to_use', 'references', 'pack_size'] as $code) {
                $definition = $definitions[$code] ?? null;
                $source     = $definition ? $english[$definition->id]->value ?? null : null;

                $value = $code === 'pack_size'
                    ? ($source && $units ? $units.' '.$word : null)
                    : $this->translateValue($source, $definition?->type);

                $match = ['product_id' => $product->id, 'attribute_id' => $definition?->id, 'locale' => $localeCode];

                if ($value) {
                    ProductAttributeValue::updateOrCreate($match, ['value' => $value]);
                } elseif ($definition) {
                    ProductAttributeValue::where($match)->delete();   // no Khmer for this English: show English
                }
            }

            // Product Q&A, matched by their English wording.
            foreach (ProductFaq::where('product_id', $product->id)->get() as $faq) {
                $question = self::TEXT[$faq->getRawOriginal('question')] ?? null;
                $answer   = self::TEXT[$faq->getRawOriginal('answer')] ?? null;

                $faq->setTranslations(self::L, ['question' => $question && $answer ? $question : null, 'answer' => $question && $answer ? $answer : null]);
            }
        }
    }

    /**
     * Khmer for a stored English value, or null when a sentence is not in
     * self::TEXT (the page then shows the English).
     */
    private function translateValue(?string $english, ?string $type): ?string
    {
        if ($english === null || trim($english) === '') {
            return null;
        }

        if ($type === 'richtext') {
            if (! preg_match_all('#<p>(.*?)</p>#su', $english, $m) || preg_replace('#<p>.*?</p>#su', '', $english) !== '') {
                return null;
            }
            $parts = array_map(fn ($inner) => self::TEXT[$inner] ?? null, $m[1]);

            return in_array(null, $parts, true) ? null : implode('', array_map(fn ($p) => '<p>'.$p.'</p>', $parts));
        }

        $lines = [];
        foreach (preg_split('/\R/u', trim($english)) as $line) {
            preg_match('/^(\[[a-z0-9-]+\]\s*)?(.*)$/u', trim($line), $m);   // keep "[icon] " step prefixes
            $text = self::TEXT[$m[2]] ?? null;
            if ($text === null) {
                return null;
            }
            $lines[] = $m[1].$text;
        }

        return implode("\n", $lines);
    }

    private function nav(): void
    {
        $labels = [
            'Our products'   => ['ផលិតផលរបស់យើង', 'ស្វែងយល់ពីផលិតផលមេដំបែប្រូបាយអូទិក {brand}។', 'ស្វែងយល់ពីផលិតផលទាំងអស់'],
            'Gut health'     => ['សុខភាពពោះវៀន', null, null],
            'Our uniqueness' => ['ភាពពិសេសរបស់យើង', null, null],
            'Our news'       => ['ព័ត៌មានរបស់យើង', null, null],
            'About us'       => ['អំពីយើង', null, null],
        ];

        foreach (NavItem::all() as $item) {
            if (isset($labels[$item->getRawOriginal('label')])) {
                [$label, $description, $cta] = $labels[$item->getRawOriginal('label')];
                $item->setTranslations(self::L, ['label' => $label, 'description' => $description, 'cta_label' => $cta]);
                continue;
            }

            // Product links in the dropdown take the product's Khmer name.
            $product = Product::all()->first(fn ($p) => str_ends_with('/'.ltrim((string) $item->url, '/'), '/'.$p->slug.'/'.$p->id.'.html'));
            if ($product && ($name = $product->translationFor(self::L, 'name'))) {
                $item->setTranslations(self::L, ['label' => plain_text($name)]);
            }
        }
    }

    private function articles(): void
    {
        $rows = [
            'support-health-gut-microbiota'             => ['របៀបជួយថែរក្សាមីក្រូប៊ីយ៉ូតាពោះវៀនឱ្យមានសុខភាពល្អ', 'របបអាហារ ការគេង ចលនា និងភាពតានតឹង សុទ្ធតែមានឥទ្ធិពលលើមីក្រុបដែលរស់នៅក្នុងពោះវៀនរបស់អ្នក។ នេះជាអ្វីដែលភស្តុតាងបានបង្ហាញ។'],
            'gut-microbiota-children'                   => ['សុខភាពពោះវៀនរបស់កូនអ្នក៖ ការកសាងមូលដ្ឋានរឹងមាំតាំងពីដំបូង', 'ឆ្នាំដំបូងនៃជីវិតជួយបង្កើតមីក្រូប៊ីយ៉ូតារបស់កុមារ។ អ្វីដែលជួយបាន អ្វីដែលគួរប្រុងប្រយ័ត្ន និងពេលណាគួរទាក់ទងគ្រូពេទ្យ។'],
            'understanding-dysbiosis'                   => ['ស្វែងយល់អំពីភាពមិនតុល្យភាពនៃមីក្រូប៊ីយ៉ូតាពោះវៀន (Dysbiosis)', 'Dysbiosis សំដៅលើភាពមិនតុល្យភាពនៃសហគមន៍មីក្រុបក្នុងពោះវៀន។ វាជាពាក្យខ្លីដែលមានប្រយោជន៍ ប៉ុន្តែងាយនឹងបកស្រាយហួសហេតុ។'],
            'living-with-ibs'                           => ['តើរោគសញ្ញាពោះវៀនរំខាន (IBS) អាចព្យាបាលឱ្យជាដាច់បានទេ?', 'IBS ត្រូវបានគ្រប់គ្រង ជាជាងព្យាបាលឱ្យជាដាច់ — ប៉ុន្តែការគ្រប់គ្រងល្អអាចជួយឱ្យវាលែងរំខានដល់ជីវិតប្រចាំថ្ងៃរបស់អ្នក។'],
            'watery-diarrhea'                           => ['រាគរូសជាទឹក៖ វាជាអ្វី និងអ្វីដែលត្រូវធ្វើមុនគេ', 'អាទិភាពដំបូងគឺការបំពេញជាតិទឹក មិនមែនការបញ្ឈប់រោគសញ្ញាភ្លាមៗនោះទេ។ នេះជាលំដាប់ដែលត្រូវអនុវត្ត។'],
            'yeast-vs-bacterial-probiotics'             => ['ប្រូបាយអូទិកមេដំបែ ធៀបនឹងប្រូបាយអូទិកបាក់តេរី', 'វាត្រូវបានដាក់ក្រោមឈ្មោះតែមួយ ប៉ុន្តែមានលក្ខណៈខុសគ្នាច្រើន — ជាពិសេសពេលប្រើជាមួយថ្នាំអង់ទីប៊ីយោទិក។'],
            'gut-health-quiz'                           => ['សំណួរប្រកួត៖ តើអ្នកស្គាល់មីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នកច្បាស់កម្រិតណា?', 'សំណួរប្រាំមួយអំពីជាតិសរសៃ ការផ្អាប់ និងជំនឿខុសៗដែលនៅតែមាន។'],
            'new-paediatric-drops-presentation'         => ['ទម្រង់ដំណក់ថ្មីសម្រាប់កុមារ', 'ដបដំណក់ដែលជួយឱ្យវាស់កម្រិតតូចៗបានត្រឹមត្រូវ និងងាយស្រួល។'],
            'a-refreshed-look-for-our-site'             => ['រូបរាងថ្មីនៃគេហទំព័ររបស់យើង', 'ការរចនាថ្មី ការរុករកកាន់តែច្បាស់ និងការគ្រប់គ្រងមាតិកាដែលក្រុមការងារក្នុងស្រុកអាចប្រើបានយ៉ាងងាយស្រួល។'],
            'notes-from-the-microbiota-congress'        => ['កំណត់ត្រាពីសន្និសីទមីក្រូប៊ីយ៉ូតាឆ្នាំនេះ', 'ប្រធានបទបីដែលគួរតាមដាន៖ ភាពជាក់លាក់នៃពូជ (strain) ការរៀបចំការសិក្សា និងគម្លាតរវាងការផ្សព្វផ្សាយ និងភស្តុតាង។'],
            'travelling-with-a-sensitive-gut'           => ['ការធ្វើដំណើរជាមួយពោះវៀនដែលងាយរងផលប៉ះពាល់', 'ការរៀបចំសាមញ្ញសម្រាប់ការធ្វើដំណើរ៖ ទឹក អនាម័យអាហារ និងអ្វីដែលត្រូវដាក់ក្នុងកាបូប។'],
            'fibre-on-your-plate'                       => ['ជាតិសរសៃនៅលើចានរបស់អ្នក៖ ផែនការងាយៗប្រចាំសប្តាហ៍', 'ការផ្លាស់ប្តូរតូចៗដែលបន្ថែមភាពចម្រុះនៃបន្លែ និងរុក្ខជាតិក្នុងមួយសប្តាហ៍។'],
            'sleep-and-your-gut'                        => ['ការគេង និងពោះវៀនរបស់អ្នក៖ ហេតុអ្វីបានជាចង្វាក់សំខាន់', 'ការគេងទៀងទាត់ជួយគាំទ្រចង្វាក់ប្រចាំថ្ងៃនៃការរំលាយអាហាររបស់អ្នក។'],
            'supporting-ibs-symptoms'                   => ['ការគាំទ្ររោគសញ្ញា IBS ក្នុងរយៈពេលវែង', 'ការគ្រប់គ្រង IBS ត្រូវបានវាស់ជាសប្តាហ៍ និងជាខែ ដែលធ្វើឱ្យរបៀបប្រើផលិតផលខុសគ្នា។'],
            'a-unique-probiotic-yeast-strain'           => ['ពូជមេដំបែប្រូបាយអូទិកតែមួយគត់', 'អ្វីដែលពាក់ព័ន្ធនឹងការកំណត់លក្ខណៈ ស្ថិរភាព និងភាពស៊ីសង្វាក់គ្នានៃផលិតកម្មនីមួយៗ។'],
            'how-to-manage-acute-diarrhea'              => ['របៀបដែលពូជមេដំបែនេះដំណើរការក្នុងករណីរាគរូសស្រួចស្រាវ', 'សេចក្តីសង្ខេបអំពីយន្តការដែលត្រូវបានសិក្សាសម្រាប់មេដំបែប្រូបាយអូទិកនេះ ក្នុងករណីរាគរូសស្រួចស្រាវ។'],
            'preventing-antibiotic-associated-diarrhea' => ['រាគរូសដែលទាក់ទងនឹងថ្នាំអង់ទីប៊ីយោទិក៖ មូលហេតុ', 'ហេតុអ្វីបានជាមេដំបែ ជាជាងបាក់តេរី ជាជម្រើសធម្មតានៅពេលប្រើថ្នាំអង់ទីប៊ីយោទិក។'],
            'preventing-travelers-diarrhea'             => ['រាគរូសពេលធ្វើដំណើរ៖ ការរៀបចំមុនពេលចេញដំណើរ', 'អ្វីដែលត្រូវយកតាម អ្វីដែលត្រូវជៀសវាង និងតួនាទីរបស់ប្រូបាយអូទិកក្នុងកាបូបធ្វើដំណើរ។'],
            'the-story-behind-saccharomyces-boulardii'  => ['រឿងរ៉ាវនៃមេដំបែប្រូបាយអូទិករបស់យើង', 'ពីការសង្កេតនៅទីវាល ទៅជាពូជដែលត្រូវបានកំណត់លក្ខណៈច្បាស់លាស់ និងមានស្តង់ដារផលិតកម្ម។'],
        ];

        foreach ($rows as $slug => [$title, $excerpt]) {
            Article::where('slug', $slug)->first()?->setTranslations(self::L, [
                'title'      => $title,
                'excerpt'    => $excerpt,
                'references' => '<p>(1) សូមបន្ថែមឯកសារយោងដែលបានអនុម័ត ដែលគាំទ្រការអះអាងក្នុងអត្ថបទនេះ។</p>',
            ]);
        }
    }

    private function pages(): void
    {
        $rows = [
            'about-us'                 => ['អំពីយើង', 'Bioflor® គឺជាម៉ាកប្រូបាយអូទិកដែលបង្កើតឡើងដោយ Biocodex និងចែកចាយនៅកម្ពុជាដោយ PPM។',
                'Bioflor® គឺជាម៉ាកប្រូបាយអូទិកដែលបង្កើតឡើងដោយ Biocodex ដែលជាក្រុមហ៊ុនឱសថបារាំង ជំនាញខាងសុខភាពមីក្រូប៊ីយ៉ូតា និងចែកចាយនៅកម្ពុជាដោយ PPM។'],
            'data-protection'          => ['ការការពារទិន្នន័យ', 'អ្វីដែលយើងប្រមូល ហេតុអ្វី និងសិទ្ធិដែលអ្នកអាចអនុវត្ត។', null],
            'cookies-settings'         => ['ការកំណត់ខូឃី', 'ជ្រើសរើសប្រភេទខូឃីដែលគេហទំព័រនេះអាចប្រើ។', null],
            'accessibility-assessment' => ['ការវាយតម្លៃភាពងាយស្រួលប្រើប្រាស់', 'ស្ថានភាពអនុលោមភាពបច្ចុប្បន្ន និងការងារដែលនៅសល់។', null],
            'terms'                    => ['លក្ខខណ្ឌប្រើប្រាស់', 'លក្ខខណ្ឌដែលអនុវត្តនៅពេលអ្នកប្រើគេហទំព័រនេះ។', null],
            'our-products'             => ['ផលិតផលរបស់យើង', null, 'ផលិតផលមេដំបែប្រូបាយអូទិក {brand}៖ ថង់ និងកាបស៊ុល។'],
            'gut-health'               => ['សុខភាពពោះវៀន', null, 'ស្វែងយល់ពីមីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នក និងរបៀបថែរក្សាវា។'],
            'our-uniqueness'           => ['ភាពពិសេសរបស់យើង', null, 'អ្វីដែលធ្វើឱ្យមេដំបែប្រូបាយអូទិកខុសប្លែក និងវិទ្យាសាស្ត្រនៅពីក្រោយវា។'],
            'our-news'                 => ['ព័ត៌មានរបស់យើង', null, 'ព័ត៌មាន ដំបូន្មានតាមរដូវកាល និងចំណុចសំខាន់ៗនៃការស្រាវជ្រាវ។'],
            'all-news'                 => ['ព័ត៌មានទាំងអស់', null, 'គន្លឹះសុខភាពពោះវៀន ព័ត៌មានផលិតផល និងការស្រាវជ្រាវពី {brand}៖ អត្ថបទទាំងអស់នៅកន្លែងតែមួយ ថ្មីបំផុតមុន។'],
            'faq'                      => ['សំណួរ និងចម្លើយ', null, 'ចម្លើយចំពោះសំណួរទូទៅអំពីប្រូបាយអូទិក និងផលិតផល {brand}។'],
            'contact-us'               => ['ទំនាក់ទំនងយើង', null, 'មានសំណួរអំពីផលិតផលរបស់យើងមែនទេ? ផ្ញើសារមកយើង។'],
            'find-in-store'            => ['កន្លែងលក់', null, 'ឱសថស្ថាន និងដៃគូអនឡាញដែលលក់ {brand}។'],
            'gut-health-quiz-intro'    => ['សំណួរប្រកួតអំពីសុខភាពពោះវៀន', null, 'សំណួរខ្លីៗដើម្បីស្វែងយល់បន្ថែមអំពីមីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នក។'],
        ];

        foreach ($rows as $slug => [$title, $subtitle, $meta]) {
            Page::where('slug', $slug)->first()?->setTranslations(self::L, ['title' => $title, 'subtitle' => $subtitle, 'meta_description' => $meta]);
        }
    }

    private function faqs(): void
    {
        $rows = [
            'What is a probiotic?' => ['តើប្រូបាយអូទិកជាអ្វី?', 'មីក្រូសរីរាង្គរស់ ដែលនៅពេលប្រើក្នុងបរិមាណគ្រប់គ្រាន់ មានគោលបំណងផ្តល់អត្ថប្រយោជន៍ដល់សុខភាព។ ភស្តុតាងភ្ជាប់ទៅនឹងពូជជាក់លាក់មួយ មិនមែនប្រភេទទាំងមូលនោះទេ។'],
            'What is the difference between a probiotic and a prebiotic?' => ['តើប្រូបាយអូទិក និងព្រីបាយអូទិកខុសគ្នាដូចម្តេច?', 'ប្រូបាយអូទិកគឺជាសរីរាង្គរស់។ ព្រីបាយអូទិកគឺជាសមាសធាតុអាហារ — ជាធម្មតាជាជាតិសរសៃ — ដែលមីក្រុបក្នុងពោះវៀនរបស់អ្នកអាចប្រើប្រាស់បាន។'],
            'Why does the strain matter so much?' => ['ហេតុអ្វីបានជាពូជមានសារៈសំខាន់ខ្លាំងម្ល៉េះ?', 'ព្រោះឥទ្ធិពលដែលបានបង្ហាញសម្រាប់ពូជមួយ មិនអាចសន្មតថាដូចគ្នាសម្រាប់ពូជផ្សេងទៀតបានទេ។ ឈ្មោះពូជនៅលើប្រអប់ គឺជាអ្វីដែលភ្ជាប់ផលិតផលទៅនឹងការស្រាវជ្រាវរបស់វា។'],
            'Can I take it with antibiotics?' => ['តើខ្ញុំអាចប្រើវាជាមួយថ្នាំអង់ទីប៊ីយោទិកបានទេ?', 'ប្រូបាយអូទិកមេដំបែត្រូវបានប្រើជាទូទៅក្នុងអំឡុងពេលប្រើថ្នាំ ព្រោះថ្នាំសម្លាប់បាក់តេរីមិនមានឥទ្ធិពលលើមេដំបែទេ។ សូមពិនិត្យពេលវេលាប្រើជាមួយឱសថការីរបស់អ្នក។'],
            'What is the right dose?' => ['តើកម្រិតប្រើត្រឹមត្រូវគឺប៉ុន្មាន?', 'សូមប្រើតាមប្រអប់ និងសន្លឹកណែនាំ ដែលអាចខុសគ្នាតាមទម្រង់ និងទីផ្សារ។ ប្រសិនបើមិនប្រាកដ សូមសួរឱសថការីរបស់អ្នក ជាជាងប៉ាន់ស្មានដោយខ្លួនឯង។'],
            'Can I mix a sachet into a hot drink?' => ['តើខ្ញុំអាចលាយថង់ម្សៅជាមួយភេសជ្ជៈក្តៅបានទេ?', 'ទេ។ សូមប្រើទឹក ឬភេសជ្ជៈត្រជាក់ — កម្តៅមិនសមស្របសម្រាប់មីក្រុបរស់ទេ។'],
            'What if I miss a dose?' => ['ចុះប្រសិនបើខ្ញុំភ្លេចប្រើមួយដង?', 'សូមប្រើដូសបន្ទាប់តាមពេលកំណត់។ កុំប្រើទ្វេដង។'],
            'How should I store it?' => ['តើត្រូវរក្សាទុកវាដោយរបៀបណា?', 'រក្សាទុកនៅសីតុណ្ហភាពក្រោម 30°C នៅកន្លែងត្រជាក់ និងស្ងួត តាមការណែនាំនៅលើប្រអប់។ ទុកឱ្យឆ្ងាយពីការមើលឃើញ និងការចាប់កាន់របស់កុមារ។'],
            'Are there people who should not take it?' => ['តើមានអ្នកណាខ្លះដែលមិនគួរប្រើវា?', 'មាន។ អ្នកដែលមានប្រព័ន្ធភាពស៊ាំចុះខ្សោយ អ្នកជំងឺធ្ងន់ធ្ងរ ឬអ្នកដែលមានបំពង់បញ្ចូលតាមសរសៃវ៉ែនកណ្តាល មិនគួរប្រើប្រូបាយអូទិកដោយគ្មានការតាមដានពីគ្រូពេទ្យឡើយ។ សូមអានសន្លឹកណែនាំ និងសួរគ្រូពេទ្យរបស់អ្នក។'],
            'Can I take it while pregnant or breastfeeding?' => ['តើខ្ញុំអាចប្រើវាពេលមានផ្ទៃពោះ ឬបំបៅដោះកូនបានទេ?', 'សូមសួរគ្រូពេទ្យ ឬឆ្មបរបស់អ្នកជាមុនសិន — ការណែនាំអាចខុសគ្នាតាមទីផ្សារ និងស្ថានភាពផ្ទាល់ខ្លួន។'],
            'Where can I buy it?' => ['តើខ្ញុំអាចទិញវានៅឯណា?', 'នៅតាមឱសថស្ថាន និងដៃគូលក់អនឡាញដែលបានជ្រើសរើស។ សូមមើលទំព័រកន្លែងលក់ សម្រាប់បញ្ជីនៅក្នុងប្រទេសរបស់អ្នក។'],
            'Why is a format I have seen not available here?' => ['ហេតុអ្វីបានជាទម្រង់ដែលខ្ញុំធ្លាប់ឃើញមិនមាននៅទីនេះ?', 'ការចុះបញ្ជី និងភាពអាចរកបាន ខុសគ្នាតាមទីផ្សារ។ ឱសថការីរបស់អ្នកអាចណែនាំផលិតផលស្រដៀងគ្នា ដែលបានអនុម័តក្នុងស្រុក។'],
        ];

        foreach (Faq::all() as $faq) {
            if ($row = $rows[$faq->getRawOriginal('question')] ?? null) {
                $faq->setTranslations(self::L, ['question' => $row[0], 'answer' => '<p>'.$row[1].'</p>']);
            }
        }
    }

    private function settings(): void
    {
        $rows = [
            'site.title'                => '{brand} | គេហទំព័រផ្លូវការ',
            'site.meta_description'     => 'មេដំបែប្រូបាយអូទិកសម្រាប់សុខភាពពោះវៀនប្រចាំថ្ងៃ៖ ស្វែងយល់ពីផលិតផល {brand} ដំបូន្មានអំពីសុខភាពពោះវៀន និងកន្លែងលក់។',
            'header.where_to_buy'       => 'កន្លែងលក់',
            'header.search_placeholder' => 'ស្វែងរក (ពាក្យគន្លឹះ ។ល។)',
            'distributor.address1'      => 'ផ្ទះលេខ ១៣៨ Bis មហាវិថីសហព័ន្ធរុស្ស៊ី (ផ្លូវលេខ ១១០)',
            'distributor.address2'      => 'ភូមិព្រៃជីសាក់ សង្កាត់ចោមចៅទី៣ ខណ្ឌពោធិ៍សែនជ័យ រាជធានីភ្នំពេញ ព្រះរាជាណាចក្រកម្ពុជា',
            'social.title'              => 'តាមដានយើង',
            'newsletter.title'          => 'ព្រឹត្តិបត្រព័ត៌មានរបស់យើង',
            'newsletter.text'           => 'គន្លឹះសុខភាពពោះវៀន ព័ត៌មានអំពី {brand} និងគំនិតសាមញ្ញៗសម្រាប់ថែរក្សាមីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នក ផ្ញើដល់ប្រអប់អ៊ីមែលរបស់អ្នកផ្ទាល់។',
            'newsletter.consent'        => 'អាសយដ្ឋានអ៊ីមែលរបស់អ្នកត្រូវបានប្រើតែសម្រាប់ផ្ញើព្រឹត្តិបត្រព័ត៌មានរបស់យើងប៉ុណ្ណោះ។ អ្នកអាចឈប់ទទួលបានគ្រប់ពេល តាមរយៈតំណភ្ជាប់នៅក្នុងអ៊ីមែលនីមួយៗ។',
            'newsletter.success_title'  => 'អ្នកបានចុះឈ្មោះរួចរាល់! សូមអរគុណដែលបានចុះឈ្មោះទទួលព្រឹត្តិបត្រព័ត៌មានរបស់យើង។',
            'newsletter.success_text'   => 'រង់ចាំទទួលគន្លឹះល្អៗសម្រាប់ពោះវៀន និងព័ត៌មានថ្មីៗពី {brand} នៅក្នុងប្រអប់អ៊ីមែលរបស់អ្នក។',
            'newsletter.popup_title'    => 'ព្រឹត្តិបត្រព័ត៌មាន',
        ];

        $locale = Locale::where('url_prefix', self::L)->first();

        foreach ($rows as $key => $value) {
            $base = Setting::where('key', $key)->first();
            if (! $base) {
                continue;
            }

            // Keep a Khmer value already edited in the admin.
            $row = Setting::firstOrNew(['key' => $key.':'.self::L]);
            $row->fill(['group' => $base->group, 'label' => $base->label.' ('.$locale->label.')', 'type' => $base->type, 'sort_order' => $base->sort_order]);
            if (! $row->exists) {
                $row->value = $value;
            }
            $row->save();
        }
    }

    /** Khmer text layered over each page's sections, by position on the page. */
    private function sections(): void
    {
        foreach ($this->sectionTexts() as $pageKey => $overlays) {
            $sections = PageSection::where('page_key', $pageKey)->orderBy('sort_order')->get();

            foreach ($sections as $i => $section) {
                if (! empty($overlays[$i])) {
                    $translations = $section->translations ?? [];
                    $translations[self::L] = $overlays[$i];
                    $section->update(['translations' => $translations]);
                }
            }
        }
    }

    private function references(int $count): array
    {
        $body = '';
        for ($i = 1; $i <= $count; $i++) {
            $body .= "<p>({$i}) សូមបន្ថែមឯកសារយោងដែលបានអនុម័ត សម្រាប់ការអះអាងទី {$i} នៅលើទំព័រនេះ។</p>";
        }

        return ['label_show' => 'មើលឯកសារយោងទាំងអស់', 'label_hide' => 'លាក់ឯកសារយោង', 'body' => $body];
    }

    private function sectionTexts(): array
    {
        $indications = [
            'title' => 'តើ {brand} អាចជួយបាននៅពេលណា?',
            'items' => [   // same order as PageSectionSeeder::INDICATIONS
                ['title' => 'រាគរូសស្រួចស្រាវដោយសារការឆ្លងមេរោគ ចំពោះមនុស្សពេញវ័យ និងកុមារ'],
                ['title' => 'ការរលាកពោះវៀនធំ និងរាគរូស ដែលទាក់ទងនឹងថ្នាំអង់ទីប៊ីយោទិក'],
                ['title' => 'ការការពារការកើតឡើងវិញនៃ <em>Clostridium difficile</em> រួមជាមួយថ្នាំ vancomycin/<wbr>metronidazole'],
                ['title' => 'រាគរូសដែលទាក់ទងនឹងការផ្តល់អាហារតាមបំពង់'],
                ['title' => 'រោគសញ្ញាពោះវៀនរំខាន (IBS)'],
            ],
        ];
        $learnCards = [
            ['title' => 'មីក្រូប៊ីយ៉ូតាពោះវៀន សុខភាពរបស់អ្នក', 'text' => 'របៀបដែលមីក្រុបក្នុងពោះវៀនរបស់អ្នកជះឥទ្ធិពលលើការរំលាយអាហារ ប្រព័ន្ធភាពស៊ាំ និងច្រើនទៀត។', 'cta_label' => 'ស្វែងយល់'],
            ['title' => 'ស្គាល់មេដំបែនៅពីក្រោយ {brand}', 'text' => 'ស្វែងយល់ពី <em>Saccharomyces boulardii</em> CNCM I-745 និងរបៀបដែលវាជួយរក្សាតុល្យភាពមីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នក។', 'cta_label' => 'ស្វែងរក'],
        ];
        $news = ['title' => 'មានអ្វីថ្មីខ្លះអំពីសុខភាពពោះវៀន?', 'intro' => 'គន្លឹះ និងការរកឃើញថ្មីៗ ដើម្បីរក្សាមីក្រូប៊ីយ៉ូតាពោះវៀនឱ្យមានសុខភាពល្អ។', 'cta_label' => 'ព័ត៌មានថ្មីៗរបស់យើង'];

        return [
            'home' => [
                ['slides' => [
                    ['title' => 'ការការពារដែលចាប់ផ្តើមពីខាងក្នុង', 'text' => '<p>{brand} មេដំបែប្រូបាយអូទិកសម្រាប់ផាសុកភាពពោះវៀនប្រចាំថ្ងៃ។</p><p>វាជួយការពារ និងស្តារតុល្យភាពនៃមីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នក។<sup>1</sup></p>', 'cta_label' => 'ស្វែងយល់ឥឡូវនេះ'],
                    ['title' => 'សុខភាពពោះវៀនដោយភាពសាមញ្ញ', 'text' => '<p>ដំបូន្មានច្បាស់លាស់ និងងាយអនុវត្ត ដើម្បីស្វែងយល់ និងថែរក្សាមីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នកជារៀងរាល់ថ្ងៃ។</p>', 'cta_label' => 'អានដំបូន្មានរបស់យើង'],
                ]],
                ['items' => [['label' => 'ការសិក្សាគ្លីនិក'], ['label' => 'ឆ្នាំនៃបទពិសោធន៍'], ['label' => 'អ្នកជំងឺចាប់តាំងពីឆ្នាំ 1980']]],
                ['title' => 'មេដំបែប្រូបាយអូទិកសម្រាប់គ្រួសារទាំងមូល', 'text' => '{brand} ជួយគាំទ្រសុខភាពពោះវៀនគ្រប់វ័យ ដោយការពារ និងស្តារមីក្រូប៊ីយ៉ូតាពោះវៀន។<sup>1</sup>', 'cta_label' => 'មើលផលិតផលទាំងអស់'],
                ['all_label' => 'ផលិតផលទាំងអស់'],
                ['title' => 'ជំនាញរបស់យើងក្នុងការអនុវត្ត', 'text' => 'ស្វែងយល់ពីរបៀបដែល {brand} គាំទ្រសុខភាពរំលាយអាហារក្នុងស្ថានភាពផ្សេងៗ។', 'item_title' => 'ការគ្រប់គ្រងរាគរូសស្រួចស្រាវ', 'item_text' => 'រាគរូសស្រួចស្រាវកើតមានញឹកញាប់ ហើយជាធម្មតាមិនយូរទេ។ ការដឹងពីរបៀបបំពេញជាតិទឹក និងពេលណាត្រូវសុំដំបូន្មាន ជួយអ្នកថែរក្សាខ្លួនឯង និងក្រុមគ្រួសារ។', 'cta_label' => 'ស្វែងយល់បន្ថែម'],
                $indications,
                ['highlight' => ['title' => 'ស្គាល់មីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នក និងរបៀបថែរក្សាវា', 'cta_label' => 'ចូលរួមសំណួរប្រកួត'], 'cards' => $learnCards],
                ['legend' => 'ពាណិជ្ជកម្មទូរទស្សន៍របស់យើង', 'transcript_title' => 'អត្ថបទប្រតិចារិកវីដេអូ', 'transcript' => '<p>បន្ថែមអត្ថបទប្រតិចារិកនៃវីដេអូរបស់អ្នកនៅទីនេះ ដើម្បីឱ្យមនុស្សគ្រប់គ្នាអាចទទួលបានខ្លឹមសារ។</p>'],
                ['surtitle' => 'សំណួរដែលអ្នកប្រហែលជាមាន', 'title' => 'តើត្រូវជ្រើសរើសប្រូបាយអូទិកដែលត្រឹមត្រូវដោយរបៀបណា?', 'cta_label' => 'ស្វែងយល់បន្ថែម'],
                ['surtitle' => 'វេជ្ជបណ្ឌិត [ឈ្មោះ] គ្រូពេទ្យទូទៅ', 'title' => '“បន្ថែមសម្រង់សម្តីខ្លីៗដែលបានអនុម័តពីបុគ្គលិកសុខាភិបាលនៅទីនេះ។”', 'cta_label' => 'ស្វែងយល់បន្ថែម'],
                $news,
                ['title' => 'អំពីយើង', 'text' => '<p>ស្គាល់ក្រុមហ៊ុននៅពីក្រោយ {brand}៖ ប្រវត្តិ ការស្រាវជ្រាវ និងការប្តេជ្ញាចិត្តចំពោះសុខភាព។</p>', 'cta_label' => 'ស្វែងយល់ពីរឿងរ៉ាវរបស់យើង'],
                $this->references(4),
            ],
            'our-products' => [
                ['title' => 'ផលិតផលរបស់យើង', 'text' => 'ស្វែងរកទម្រង់ {brand} ដែលសមស្របសម្រាប់អ្នក និងក្រុមគ្រួសារ។'],
                ['title' => 'មេដំបែប្រូបាយអូទិកមួយ ក្នុងទម្រង់ពីរ', 'text' => 'ថង់ ឬកាបស៊ុល — ជ្រើសរើសទម្រង់ដែលសមនឹងទម្លាប់ប្រចាំថ្ងៃរបស់អ្នក។'],
                ['all_label' => 'ផលិតផលទាំងអស់'],
                ['title' => 'តើទម្រង់មួយណាសមស្របសម្រាប់ខ្ញុំ?', 'text' => '<ul><li>ថង់ម្សៅងាយស្រួលលាយជាមួយទឹកបន្តិច។</li><li>កាបស៊ុលងាយស្រួលប្រើនៅផ្ទះ ឬពេលធ្វើដំណើរ។</li></ul>', 'cta_label' => 'មើលសំណួរ និងចម្លើយ'],
                $indications,
                ['cards' => $learnCards],
                $this->references(2),
            ],
            'pdp' => [
                ['title' => 'របៀបដែល {product} ដំណើរការ', 'text' => '<p>{product} មាន <em>Saccharomyces boulardii</em> CNCM I-745 ដែលជាមេដំបែប្រូបាយអូទិក ជួយស្តារតុល្យភាពមីក្រូប៊ីយ៉ូតាពោះវៀន។<sup>1</sup></p>', 'cta_label' => 'ស្វែងយល់បន្ថែម'],
                ['title' => 'ហេតុអ្វីជ្រើសរើសមេដំបែប្រូបាយអូទិក?', 'intro' => 'ហេតុផលបួនយ៉ាងដើម្បីជ្រើសរើស <em>Saccharomyces boulardii</em> CNCM I-745។', 'items' => [
                    ['title' => 'ប្រសិទ្ធភាព', 'text' => 'ត្រូវបានសិក្សាក្នុងការសាកល្បងគ្លីនិកជាច្រើន។<sup>2</sup>'],
                    ['title' => 'សុវត្ថិភាព', 'text' => 'ត្រូវបានប្រើដោយមនុស្សរាប់លាននាក់ទូទាំងពិភពលោក។<sup>3</sup>'],
                    ['title' => 'គុណភាព', 'text' => 'ផលិតតាមស្តង់ដារគុណភាពឱសថ។'],
                    ['title' => 'ទទួលស្គាល់', 'text' => 'ត្រូវបានលើកឡើងក្នុងអនុសាសន៍អន្តរជាតិ។<sup>4</sup>'],
                ]],
                ['title' => 'ស្វែងយល់ពីផលិតផលផ្សេងទៀតរបស់យើង', 'text' => 'មេដំបែប្រូបាយអូទិកដូចគ្នា ក្នុងទម្រង់មួយទៀតសម្រាប់ទម្លាប់របស់អ្នក។', 'cta_label' => 'មើលផលិតផលទាំងអស់'],
                ['all_label' => 'ផលិតផលទាំងអស់'],
                $news,
            ],
            'gut-health' => [
                ['title' => 'សុខភាពពោះវៀន'],
                ['title' => 'សុខភាពពោះវៀន', 'subtitle' => 'មីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នកធ្វើច្រើនជាងជួយរំលាយអាហារ។ ស្វែងយល់ពីរបៀបដែលវាដំណើរការ និងរបៀបថែរក្សាវា។'],
                ['title' => 'មីក្រូប៊ីយ៉ូតាពោះវៀនដោយសង្ខេប', 'text' => '<p>មីក្រូសរីរាង្គរាប់ពាន់ពាន់លានរស់នៅក្នុងពោះវៀនរបស់អ្នក។ នៅពេលមានតុល្យភាព ពួកវាជួយការរំលាយអាហារ ការពារប្រឆាំងនឹងមីក្រុបដែលមិនចង់បាន និងទំនាក់ទំនងជាមួយប្រព័ន្ធភាពស៊ាំរបស់អ្នក។</p>', 'cta_label' => 'ស្វែងយល់អំពី Dysbiosis'],
                ['title' => 'ស្វែងយល់ពីពោះវៀនរបស់អ្នក', 'intro' => 'មូលដ្ឋានគ្រឹះនៃមីក្រូប៊ីយ៉ូតាពោះវៀន សម្រាប់គ្រប់វ័យ។'],
                ['title' => 'រោគសញ្ញា និងដំបូន្មាន', 'intro' => 'អ្វីដែលត្រូវធ្វើនៅពេលពោះវៀនរបស់អ្នកបាត់បង់តុល្យភាព។'],
                ['title' => 'តើអ្នកស្គាល់ពោះវៀនរបស់អ្នកច្បាស់កម្រិតណា?', 'cta_label' => 'ចូលរួមសំណួរប្រកួត'],
            ],
            'our-uniqueness' => [
                ['title' => 'ភាពពិសេសរបស់យើង'],
                ['title' => 'ភាពពិសេសរបស់យើង', 'subtitle' => 'មេដំបែប្រូបាយអូទិកដែលត្រូវបានសិក្សាអស់ជាច្រើនទសវត្សរ៍ ដែលដំណើរការខុសពីប្រូបាយអូទិកបាក់តេរី។'],
                ['title' => 'អ្វីដែលធ្វើឱ្យមេដំបែរបស់យើងខុសប្លែក', 'items' => [
                    ['title' => 'មេដំបែ មិនមែនបាក់តេរី', 'text' => 'ប្រូបាយអូទិកប្រភេទផ្សេង ដែលមានរបៀបដំណើរការផ្ទាល់ខ្លួន។<sup>1</sup>'],
                    ['title' => 'ពូជដែលត្រូវបានសិក្សាយ៉ាងច្រើន', 'text' => 'គាំទ្រដោយការស្រាវជ្រាវគ្លីនិកយ៉ាងទូលំទូលាយ។<sup>2</sup>'],
                    ['title' => 'គុណភាពឱសថ', 'text' => 'ផលិតកម្មនីមួយៗត្រូវបានផលិតតាមលក្ខណៈបច្ចេកទេសដូចគ្នា។'],
                    ['title' => 'ការប្រើប្រាស់ជាច្រើនទសវត្សរ៍', 'text' => 'ត្រូវបានទុកចិត្តដោយគ្រួសារនៅក្នុងប្រទេសជាច្រើន។<sup>3</sup>'],
                ]],
                ['html' => '<h2>ពូជមេដំបែប្រូបាយអូទិកតែមួយគត់</h2><p><em>Saccharomyces boulardii</em> CNCM I-745 គឺជាពូជជាក់លាក់មួយ។ លក្ខណៈសម្បត្តិរបស់វាភ្ជាប់ទៅនឹងពូជនោះ ដែលជាហេតុធ្វើឱ្យឈ្មោះពេញមានសារៈសំខាន់។</p><p><a class="btn btn--pink" href="a-unique-probiotic-yeast-strain.html">អានបន្ថែម</a></p>'],
                ['title' => 'របៀបដែល {brand} ជួយ', 'intro' => 'ស្ថានភាពដែលមេដំបែប្រូបាយអូទិកអាចគាំទ្រពោះវៀនរបស់អ្នក។'],
                $this->references(3),
            ],
            'our-news' => [
                ['title' => 'ព័ត៌មានរបស់យើង'],
                ['title' => 'ព័ត៌មានរបស់យើង', 'subtitle' => 'ដំបូន្មានតាមរដូវកាល គំនិតអាហារូបត្ថម្ភ និងចំណុចសំខាន់ៗនៃការស្រាវជ្រាវអំពីសុខភាពពោះវៀន។'],
                ['label' => 'រកមើលតាមប្រធានបទ'],
                null,
                ['title' => 'ចុងក្រោយតាមប្រធានបទ'],
                ['title' => 'តាមរដូវកាល', 'intro' => 'ដំបូន្មានសម្រាប់ការធ្វើដំណើរ ថ្ងៃឈប់សម្រាក និងការផ្លាស់ប្តូររដូវ។', 'cta_label' => 'មើលទាំងអស់'],
                ['title' => 'អាហារូបត្ថម្ភ', 'intro' => 'គំនិតអាហារដែលល្អសម្រាប់មីក្រូប៊ីយ៉ូតាពោះវៀនរបស់អ្នក។', 'cta_label' => 'មើលទាំងអស់'],
                ['title' => 'របៀបរស់នៅ', 'intro' => 'ការគេង ចលនា និងទម្លាប់ប្រចាំថ្ងៃ។', 'cta_label' => 'មើលទាំងអស់'],
                ['title' => 'នវានុវត្តន៍ និងការស្រាវជ្រាវ', 'intro' => 'អ្វីដែលការស្រាវជ្រាវថ្មីៗប្រាប់យើងអំពីមីក្រូប៊ីយ៉ូម។', 'cta_label' => 'មើលទាំងអស់'],
                ['title' => 'ព័ត៌មានក្រុមហ៊ុន', 'intro' => 'ព័ត៌មានពីក្រុមការងារ {brand}។', 'cta_label' => 'មើលទាំងអស់'],
                ['title' => 'ព័ត៌មានផលិតផល', 'intro' => 'ទម្រង់ថ្មីៗ និងព័ត៌មានថ្មីអំពីផលិតផល។', 'cta_label' => 'មើលទាំងអស់'],
            ],
            'about-us' => [
                ['breadcrumb' => 'អំពីយើង', 'title' => 'អំពីយើង', 'text' =>
                    '<p>Bioflor® គឺជាម៉ាកប្រូបាយអូទិកដែលបង្កើតឡើងដោយ Biocodex ដែលជាក្រុមហ៊ុនឱសថបារាំង ជំនាញខាងសុខភាពមីក្រូប៊ីយ៉ូតា។</p>'
                    .'<p>Bioflor® មានផ្ទុក <em>Saccharomyces boulardii</em> CNCM I-745 ដែលជាមេដំបែប្រូបាយអូទិកដែលត្រូវបានស្រាវជ្រាវយ៉ាងទូលំទូលាយ។</p>'
                    .'<p>នៅកម្ពុជា Bioflor® ត្រូវបានចែកចាយដោយ PPM ដែលប្តេជ្ញាបង្កើនលទ្ធភាពទទួលបានផលិតផលថែទាំសុខភាពដែលគួរឱ្យទុកចិត្ត និងគាំទ្រសុខភាពប្រព័ន្ធរំលាយអាហារ។</p>'],
                ['html' => '<h2>បេសកកម្មរបស់យើង</h2>', 'aside' =>
                    '<p class="text-intro">បេសកកម្មរបស់យើងគឺលើកកម្ពស់សុខភាពប្រព័ន្ធរំលាយអាហារ តាមរយៈការផ្តល់នូវដំណោះស្រាយប្រូបាយអូទិកដែលគួរឱ្យទុកចិត្ត និងផ្អែកលើវិទ្យាសាស្ត្រ។</p>'
                    .'<p>តាមរយៈ Bioflor® ដែលមានផ្ទុក <em>Saccharomyces boulardii</em> CNCM I-745 យើងប្តេជ្ញាគាំទ្រសុខភាពពោះវៀន ចែករំលែកព័ត៌មានសុខភាពដែលអាចទុកចិត្តបាន និងបង្កើនលទ្ធភាពទទួលបានសេវាថែទាំសុខភាពប្រកបដោយគុណភាពនៅកម្ពុជា។</p>'],
                ['title' => 'ប្រវត្តិរបស់យើង', 'items' => [   // years come from the English
                    ['title' => 'ការរកឃើញ', 'text' => 'Henri Boulard បានរកឃើញ <em>Saccharomyces boulardii</em> នៅឥណ្ឌូចិន។'],
                    ['title' => 'ការបង្កើតក្រុមហ៊ុន Biocodex', 'text' => 'Biocodex ត្រូវបានបង្កើតឡើងនៅប្រទេសបារាំង ដើម្បីអភិវឌ្ឍ និងធ្វើពាណិជ្ជកម្មមេដំបែប្រូបាយអូទិកនេះ។'],
                    ['title' => 'សមិទ្ធផលផ្នែកវេជ្ជសាស្ត្រ', 'text' => '<em>Saccharomyces boulardii</em> CNCM I-745 ត្រូវបានចុះបញ្ជីជាផ្លូវការជាផលិតផលឱសថ។'],
                    ['title' => 'ការដាក់លក់ Bioflor® ឡើងវិញនៅកម្ពុជា', 'text' => 'Bioflor® ត្រូវបានដាក់លក់ឡើងវិញនៅកម្ពុជា តាមរយៈភាពជាដៃគូថ្មីជាមួយ PPM ដែលត្រូវបានសម្គាល់ដោយព្រឹត្តិការណ៍ដាក់លក់ឡើងវិញក្នុងខែតុលា ឆ្នាំ ២០២៦។'],
                ]],
                ['title' => 'ការស្រាវជ្រាវគឺជាស្នូលនៃអ្វីដែលយើងធ្វើ', 'text' => '<p>ក្រុមការងាររបស់យើងធ្វើការជាមួយគ្រូពេទ្យ និងអ្នកវិទ្យាសាស្ត្រ ដើម្បីបន្តពង្រឹងការយល់ដឹងរបស់យើងអំពីមីក្រូប៊ីយ៉ូតាពោះវៀន។</p>', 'cta_label' => 'ភាពពិសេសរបស់យើង'],
                ['title' => 'គុណតម្លៃរបស់យើង', 'items' => [
                    ['title' => 'វិទ្យាសាស្ត្រ', 'text' => 'ភស្តុតាងមុនគេ ក្នុងគ្រប់ផលិតផល និងគ្រប់សារ។'],
                    ['title' => 'គុណភាព', 'text' => 'ស្តង់ដារខ្ពស់ដូចគ្នា រាល់ផលិតកម្មនីមួយៗ។'],
                    ['title' => 'ការយកចិត្តទុកដាក់', 'text' => 'ផលិតផល និងដំបូន្មានសម្រាប់គ្រប់វ័យ។'],
                    ['title' => 'ការទទួលខុសត្រូវ', 'text' => 'ធ្វើសកម្មភាពសម្រាប់អ្នកជំងឺ ដៃគូ និងភពផែនដី។'],
                ]],
            ],
            'faq' => [
                ['title' => 'សំណួរ និងចម្លើយ'],
                ['title' => 'សំណួរ និងចម្លើយ', 'subtitle' => 'ចម្លើយចំពោះសំណួរទូទៅអំពីប្រូបាយអូទិក និងផលិតផល {brand}។'],
                null,
                ['title' => 'រកមិនឃើញចម្លើយរបស់អ្នកមែនទេ?', 'text' => 'ក្រុមការងាររបស់យើងរីករាយជួយអ្នក។ អ្នកក៏អាចសួរឱសថការីរបស់អ្នកផងដែរ។', 'buttons' => [['label' => 'ទំនាក់ទំនងយើង'], ['label' => 'កន្លែងលក់']]],
                $this->references(2),
            ],
            'gut-health-quiz-intro' => [
                ['html' => '<h1 class="h1-sm">តើអ្នកស្គាល់ពោះវៀនរបស់អ្នកច្បាស់កម្រិតណា?</h1><p class="text-intro">សំណួរខ្លីៗប្រាំ ដើម្បីសាកល្បងចំណេះដឹងរបស់អ្នកអំពីមីក្រូប៊ីយ៉ូតាពោះវៀន — និងរៀនអ្វីថ្មីៗ។</p><p><a class="btn btn--pink" href="gut-health-quiz.html">ចាប់ផ្តើមសំណួរប្រកួត</a></p>'],
                $this->references(1),
            ],
        ];
    }
}
