<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\Faq;
use App\Models\NavItem;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductFaq;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Round trip for translating the site content with an AI chat (ChatGPT, Gemini):
 *
 *   php artisan translations:ai export            → lang/ai-translate/km-*.json
 *   (translate each file in the chat, save its answer over the file)
 *   php artisan translations:ai import --dry-run  → check
 *   php artisan translations:ai import            → save
 *
 * Each entry is "key": {"en": English, "km": Khmer}. Only "km" is read back.
 * Covers products and their attribute values, product Q&A, Q&A, pages,
 * articles, menu, categories, attribute names, settings and page sections.
 * Interface words live in lang/km.json instead.
 */
class AiTranslations extends Command
{
    protected $signature = 'translations:ai {action : export or import} {--locale=km} {--dry-run : import: report without saving}';

    protected $description = 'Export site content for AI translation, or import the translated files';

    /** Model => fields; the Translation rows use the locale prefix (km). */
    private const MODELS = [
        Setting::class          => null,   // handled separately
        NavItem::class          => ['label', 'description', 'cta_label'],
        Category::class         => ['name', 'description'],
        ProductAttribute::class => ['name', 'help'],
        Page::class             => ['title', 'subtitle', 'meta_description', 'body'],
        Product::class          => ['name', 'short_description', 'meta_title', 'meta_description'],
        ProductFaq::class       => ['question', 'answer'],
        Faq::class              => ['question', 'answer'],
        Article::class          => ['title', 'excerpt', 'body', 'references'],
    ];

    /** Product attributes whose values are words (not codes, numbers or colours). */
    private const ATTRIBUTE_TYPES = ['text', 'textarea', 'richtext', 'list'];
    private const ATTRIBUTE_SKIP  = ['active_ingredient', 'registration_number', 'gtin'];

    /** Page-section keys that hold links, files or options rather than words. */
    private const SECTION_SKIP = [
        'image', 'image_mobile', 'video', 'url', 'cta_url', 'icon', 'color', 'layout', 'variant', 'align',
        'background', 'shape', 'image_shape', 'image_side', 'title_tag', 'title_style', 'list_style', 'type',
        'page_key', 'slugs', 'group', 'category', 'product_text', 'interval', 'ref', 'year',
    ];

    private const SETTING_TYPES = ['text', 'textarea', 'richtext'];

    /** Names and numbers that stay as they are in every language. */
    private const SETTING_SKIP = ['brand.name', 'brand.strain', 'company.name', 'distributor.name', 'distributor.phone'];

    /** English characters per file, so a chat can answer one file in full. */
    private const FILE_SIZE = 12000;

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'export' => $this->export(),
            'import' => $this->import(),
            default  => $this->fail('Action must be export or import.'),
        };
    }

    private function dir(): string
    {
        return lang_path('ai-translate');
    }

    // ---------------------------------------------------------------- export

    private function export(): int
    {
        $locale = (string) $this->option('locale');
        $groups = [
            'site'     => $this->settings($locale) + $this->models([NavItem::class, Category::class, ProductAttribute::class, Page::class], $locale, ['body']),
            'products' => $this->models([Product::class, ProductFaq::class, Faq::class], $locale) + $this->attributeValues(),
            'sections' => $this->sections($locale),
            'articles' => $this->models([Article::class], $locale),
        ];

        File::ensureDirectoryExists($this->dir());
        File::delete(File::glob($this->dir().'/'.$locale.'-*.json'));

        $n = 0;
        foreach ($groups as $name => $entries) {
            $chunk = [];
            $size  = 0;
            foreach ($entries as $key => $entry) {
                if ($chunk && $size + mb_strlen($entry['en']) > self::FILE_SIZE) {
                    $this->write($locale, ++$n, $name, $chunk);
                    [$chunk, $size] = [[], 0];
                }
                $chunk[$key] = $entry;
                $size += mb_strlen($entry['en']);
            }
            if ($chunk) {
                $this->write($locale, ++$n, $name, $chunk);
            }
        }

        $total   = array_sum(array_map('count', $groups));
        $missing = array_sum(array_map(fn ($g) => count(array_filter($g, fn ($e) => $e['km'] === '')), $groups));
        $this->info("{$total} texts in {$n} files ({$missing} not translated yet) in lang/ai-translate/");

        return self::SUCCESS;
    }

    private function write(string $locale, int $n, string $name, array $entries): void
    {
        $file = sprintf('%s/%s-%02d-%s.json', $this->dir(), $locale, $n, $name);
        File::put($file, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
        $this->line('  '.basename($file).' — '.count($entries).' texts');
    }

    /** @param list<class-string> $classes */
    private function models(array $classes, string $locale, array $skip = []): array
    {
        $entries = [];
        foreach ($classes as $class) {
            $short = class_basename($class);
            $map   = $class::translationMap($locale);
            $query = $class === Article::class ? $class::whereNotNull('published_at') : $class::query();

            foreach ($query->get() as $record) {
                foreach (array_diff(self::MODELS[$class], $skip) as $field) {
                    $en = (string) $record->getRawOriginal($field);
                    if (trim(strip_tags($en)) !== '') {
                        $entries["t|{$short}|{$record->getKey()}|{$field}"] = ['en' => $en, 'km' => (string) ($map[$record->getKey()][$field] ?? '')];
                    }
                }
            }
        }

        return $entries;
    }

    private function settings(string $locale): array
    {
        $entries = [];
        $all     = Setting::pluck('value', 'key');

        foreach (Setting::whereIn('type', self::SETTING_TYPES)->whereNotIn('key', self::SETTING_SKIP)->where('key', 'not like', '%:%')->orderBy('sort_order')->get() as $setting) {
            if (trim((string) $setting->value) !== '') {
                $entries["s|{$setting->key}"] = ['en' => (string) $setting->value, 'km' => (string) ($all[$setting->key.':'.$locale] ?? '')];
            }
        }

        return $entries;
    }

    private function attributeValues(): array
    {
        $attributes = ProductAttribute::whereIn('type', self::ATTRIBUTE_TYPES)->whereNotIn('code', self::ATTRIBUTE_SKIP)->pluck('code', 'id');
        $values     = ProductAttributeValue::whereIn('attribute_id', $attributes->keys())->get()->groupBy(fn ($v) => $v->product_id.'|'.$v->attribute_id);
        $entries    = [];

        foreach ($values as $pair => $rows) {
            $en = (string) $rows->firstWhere('locale', '')?->value;
            if (trim(strip_tags($en)) === '') {
                continue;
            }
            [$product, $attribute] = explode('|', $pair);
            $entries["a|{$product}|{$attributes[$attribute]}"] = ['en' => $en, 'km' => (string) $rows->firstWhere('locale', 'km_KH')?->value];
        }

        return $entries;
    }

    private function sections(string $locale): array
    {
        $entries = [];

        foreach (PageSection::where('is_visible', true)->orderBy('page_key')->orderBy('sort_order')->get() as $section) {
            $km = $section->translations[$locale] ?? [];
            foreach ($this->texts((array) $section->payload) as $path => $en) {
                $entries["p|{$section->id}|{$path}"] = ['en' => $en, 'km' => (string) (is_string($v = data_get($km, $path)) ? $v : '')];
            }
        }

        return $entries;
    }

    /** Flatten the word values of a section payload to dot paths. */
    private function texts(array $data, string $prefix = ''): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            if (is_array($value)) {
                $out += $this->texts($value, $path);
            } elseif (is_string($value) && ! in_array((string) $key, self::SECTION_SKIP, true)
                && preg_match('/\p{L}{2,}/u', strip_tags($value))
                && ! preg_match('#^(https?:|/|\#|mailto:|tel:)|^[\w./-]+\.(html|webp|png|jpe?g|svg|mp4)$#i', $value)) {
                $out[$path] = $value;
            }
        }

        return $out;
    }

    // ---------------------------------------------------------------- import

    private function import(): int
    {
        $locale = (string) $this->option('locale');
        $dry    = (bool) $this->option('dry-run');
        $files  = File::glob($this->dir().'/'.$locale.'-*.json');

        if (! $files) {
            return $this->fail('No lang/ai-translate/'.$locale.'-*.json files. Run the export first.');
        }

        $saved = $same = 0;
        $problems = [];
        $sections = [];

        foreach ($files as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (! is_array($data)) {
                $problems[] = basename($file).': not valid JSON ('.json_last_error_msg().') — file skipped';
                continue;
            }

            foreach ($data as $key => $entry) {
                $km = is_array($entry) ? trim((string) ($entry['km'] ?? '')) : '';
                $en = is_array($entry) ? (string) ($entry['en'] ?? '') : '';
                if ($km === '') {
                    continue;
                }
                if ($issue = $this->check($en, $km)) {
                    $problems[] = "{$key}: {$issue} — skipped";
                    continue;
                }

                $parts = explode('|', (string) $key);
                $changed = match ($parts[0]) {
                    't'     => $this->saveModel($parts, $locale, $km, $dry),
                    'a'     => $this->saveAttribute($parts, $km, $dry),
                    's'     => $this->saveSetting($parts[1] ?? '', $locale, $km, $dry),
                    'p'     => $this->queueSection($sections, $parts, $locale, $km),
                    default => null,
                };

                if ($changed === null) {
                    $problems[] = "{$key}: unknown key or record — skipped";
                } else {
                    $changed ? $saved++ : $same++;
                }
            }
        }

        foreach ($sections as $id => $translations) {
            if (! $dry) {
                PageSection::whereKey($id)->first()?->forceFill(['translations' => $translations])->save();
            }
        }

        foreach ($problems as $problem) {
            $this->warn($problem);
        }
        $this->info(($dry ? '[dry run] would save ' : 'Saved ')."{$saved} changed texts; {$same} unchanged; ".count($problems).' skipped.');

        return self::SUCCESS;
    }

    /** Placeholders, tags and script mistakes that would break the page. */
    private function check(string $en, string $km): ?string
    {
        preg_match_all('/\{[a-z_]+\}|:[a-z]+\b/', $en, $a);
        preg_match_all('/\{[a-z_]+\}|:[a-z]+\b/', $km, $b);
        if ($missing = array_diff($a[0], $b[0])) {
            return 'placeholder missing: '.implode(' ', array_unique($missing));
        }
        if (preg_match('/[\x{0E00}-\x{0E7F}]/u', $km)) {
            return 'contains Thai letters';
        }
        if (preg_match('/[\x{3040}-\x{30FF}\x{3400}-\x{9FFF}\x{AC00}-\x{D7AF}]/u', $km)) {
            return 'contains Chinese, Japanese or Korean characters';
        }
        preg_match_all('#</?([a-z0-9]+)#i', $en, $ta);
        preg_match_all('#</?([a-z0-9]+)#i', $km, $tb);
        if (count($ta[0]) !== count($tb[0])) {
            return 'HTML tags differ from the English';
        }
        if (preg_match('/<script|on\w+\s*=|javascript:/i', $km)) {
            return 'contains script';
        }

        return null;
    }

    private function saveModel(array $parts, string $locale, string $km, bool $dry): ?bool
    {
        [, $short, $id, $field] = array_pad($parts, 4, null);
        $class = collect(array_keys(self::MODELS))->first(fn ($c) => class_basename($c) === $short);
        if (! $class || ! in_array($field, (array) self::MODELS[$class], true) || ! ($record = $class::find($id))) {
            return null;
        }
        if (($class::translationMap($locale)[$record->getKey()][$field] ?? null) === $km) {
            return false;
        }
        if (! $dry) {
            $record->setTranslations($locale, [$field => $km]);
        }

        return true;
    }

    private function saveAttribute(array $parts, string $km, bool $dry): ?bool
    {
        [, $productId, $code] = array_pad($parts, 3, null);
        $attribute = ProductAttribute::where('code', $code)->first();
        if (! $attribute || ! Product::whereKey($productId)->exists()) {
            return null;
        }
        $match = ['product_id' => $productId, 'attribute_id' => $attribute->id, 'locale' => 'km_KH'];
        if (ProductAttributeValue::where($match)->value('value') === $km) {
            return false;
        }
        if (! $dry) {
            ProductAttributeValue::updateOrCreate($match, ['value' => $km]);
        }

        return true;
    }

    private function saveSetting(string $key, string $locale, string $km, bool $dry): ?bool
    {
        $base = Setting::where('key', $key)->first();
        if (! $base) {
            return null;
        }
        if (Setting::where('key', $key.':'.$locale)->value('value') === $km) {
            return false;
        }
        if (! $dry) {
            Setting::updateOrCreate(['key' => $key.':'.$locale], [
                'group' => $base->group, 'label' => $base->label.' (Khmer)', 'type' => $base->type,
                'value' => $km, 'sort_order' => $base->sort_order,
            ]);
        }

        return true;
    }

    /** Section overlays are collected per section and saved once. */
    private function queueSection(array &$sections, array $parts, string $locale, string $km): ?bool
    {
        [, $id, $path] = array_pad($parts, 3, null);
        if (! isset($sections[$id])) {
            $section = PageSection::find($id);
            if (! $section) {
                return null;
            }
            $sections[$id] = (array) $section->translations;
        }
        if (data_get($sections[$id], $locale.'.'.$path) === $km) {
            return false;
        }
        data_set($sections[$id], $locale.'.'.$path, $km);

        return true;
    }
}
