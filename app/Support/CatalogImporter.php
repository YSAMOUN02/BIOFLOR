<?php

namespace App\Support;

use App\Models\AttributeGroup;
use App\Models\AttributeSet;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductFaq;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Imports a product folder: a spreadsheet (.xlsx or .csv) listing the range,
 * plus pack-shot images.
 *
 * Spreadsheet (the newest .xlsx/.csv in the folder): the first row containing
 * a "Description"/"Product"/"Name" header is the header row. Known columns map
 * to attributes:
 *
 *   Description / Product / Name   → product name   ("BIOFLOR 250 MG 10 SACHETS")
 *   Composition / Ingredient       → active_ingredient (+ ingredients text)
 *   Packaging / Pack               → pack_size and units_per_pack
 *   Indication / Usage             → usage (one indication per line)
 *   Dosage / Posology, Storage, Precautions, How to use, Age
 *                                  → the attribute of the same purpose
 *   Id / Ref / SKU (numeric)       → product id used in the URL
 *   No. / #                        → ignored
 *
 * A column named like an existing attribute (code or name) fills it; any
 * other column becomes an attribute of its own (created in "Key facts"), so a
 * new kind of data only needs a new column. Strength ("250 MG") and the
 * dosage form ("SACHETS") are read from the name and packaging.
 *
 * Rows without a product name continue the product above (one indication or
 * storage line per row); a line starting in lower case continues the previous
 * line. Text below the table (a heading in the "No." column) is read as
 * product Q&A: a heading naming a dosage form ("… Sachet", "… Capsules")
 * selects the products, a line ending in "?" is a question and the lines
 * after it are its answer. Changed English text removes the now outdated
 * translations, so other languages show English until re-translated.
 *
 * Images are matched to rows by dosage form, unit count and strength found in
 * the file name ("Bioflor 250mg B.10Sachet_1.png"), resized and saved to
 * public/uploads/products.
 */
class CatalogImporter
{
    private const FORMS = [
        'sachet'  => ['sachet', 'sachets', 'sach'],
        'capsule' => ['capsule', 'capsules', 'caps', 'cap'],
        'stick'   => ['stick', 'sticks'],
        'vial'    => ['vial', 'vials'],
        'drops'   => ['drop', 'drops'],
        'tablet'  => ['tablet', 'tablets', 'tab', 'tabs'],
    ];

    private const FORM_CATEGORY = [
        'sachet' => ['sachets', 'Sachets'], 'capsule' => ['capsules', 'Capsules'], 'stick' => ['sticks', 'Sticks'],
        'vial' => ['vials', 'Vials'], 'drops' => ['drops', 'Drops'], 'tablet' => ['tablets', 'Tablets'],
    ];

    private const FORM_COLOUR = [
        'sachet' => '#e5197e', 'capsule' => '#8cc63f', 'stick' => '#f08a24',
        'vial' => '#6c4ab6', 'drops' => '#4ec2eb', 'tablet' => '#3aa7d9',
    ];

    /** Packaging header, tolerant of spellings such as "Parkaging". */
    private const PACKAGING = '/^pa(r)?c?k(a|i)?g?/i';

    private const NAME_HEADER = '/^(description|product( name)?|name)$/i';

    private const NUMBER_HEADER = '/^(no\.?|#|n°)$/i';

    /** Column header → attribute code, for columns named differently from the attribute. */
    private const ALIASES = [
        '/^(indications?|usage|uses?)$/i'                    => 'usage',
        '/^(dosage|posology|doses?)$/i'                      => 'dosage',
        '/^(storage|store|conservation)$/i'                  => 'storage',
        '/^(precautions?|warnings?|contra-?indications?)$/i' => 'precautions',
        '/^(how to use|administration|directions?)$/i'       => 'how_to_use',
        '/^(age|age range|suitable for)$/i'                  => 'age_range',
    ];

    /** Spelling slips seen in product lists. */
    private const TYPOS = [
        '/\bsaccharomyces+\b/i' => 'Saccharomyces',
        '/\bvancomycine\b/i'    => 'vancomycin',
    ];

    /** @var list<string> */
    public array $log = [];

    /** @var list<string> text found below the product table */
    private array $notes = [];

    /**
     * @return Collection<int, Product> the imported products, in sheet order
     */
    public function import(string $folder, bool $dryRun = false): Collection
    {
        if (! is_dir($folder)) {
            throw new RuntimeException("Folder not found: {$folder}");
        }

        // The newest list wins; skip Excel's "~$name.xlsx" lock files.
        $sheet = collect(glob($folder.'/*.{xlsx,csv}', GLOB_BRACE))
            ->reject(fn ($file) => str_starts_with(basename($file), '~$'))
            ->sortByDesc(fn ($file) => filemtime($file))
            ->first();

        if (! $sheet) {
            throw new RuntimeException("No .xlsx or .csv product list in {$folder}");
        }

        $rows   = $this->rows($sheet);
        $faqs   = $this->questions($this->notes);
        $images = collect(glob($folder.'/*.{png,jpg,jpeg,webp,PNG,JPG,JPEG,WEBP}', GLOB_BRACE));
        $this->log[] = 'Sheet: '.basename($sheet).' — '.count($rows).' product row(s), '.count($faqs).' question(s); '.$images->count().' image(s).';

        $imported = collect();

        foreach ($rows as $position => $row) {
            $data    = $this->interpret($row) + ['sort_order' => $position + 1];
            $image   = $this->matchImage($images, $data);
            $mine    = array_values(array_filter($faqs, fn ($faq) => $faq['form'] === null || $faq['form'] === $data['form']));
            $this->log[] = sprintf('  %s  →  /%s  (form: %s, %s units, %s mg)%s%s',
                $data['name'], $data['slug'], $data['form'] ?? '?', $data['units'] ?? '?', $data['strength'] ?? '?',
                $image ? '  image: '.basename($image) : '  (no image matched)',
                $mine ? '  Q&A: '.count($mine) : '');

            if (! $dryRun) {
                $product = $this->save($data, $image);
                if ($mine) {
                    $this->saveQuestions($product, $mine);
                }
                $imported->push($product);
            }
        }

        return $imported;
    }

    // ------------------------------------------------------------------ read

    /** @return list<array<string, string>> product rows keyed by header (multi-row cells joined by new lines) */
    public function rows(string $file): array
    {
        $grid = str_ends_with(strtolower($file), '.csv') ? $this->readCsv($file) : $this->readXlsx($file);

        $headerIndex = null;
        foreach ($grid as $i => $cells) {
            foreach ($cells as $cell) {
                if (preg_match(self::NAME_HEADER, trim($cell))) {
                    $headerIndex = $i;
                    break 2;
                }
            }
        }

        if ($headerIndex === null) {
            throw new RuntimeException('No header row with a "Description" (or "Product"/"Name") column found.');
        }

        $headers    = array_map(fn ($h) => $this->clean($h), $grid[$headerIndex]);
        $nameHeader = collect($headers)->first(fn ($h) => preg_match(self::NAME_HEADER, $h));
        $numHeader  = collect($headers)->first(fn ($h) => preg_match(self::NUMBER_HEADER, $h));
        $rows       = [];
        $this->notes = [];

        foreach (array_slice($grid, $headerIndex + 1) as $cells) {
            if ($this->notes) {
                array_push($this->notes, ...$this->texts($cells));
                continue;
            }

            $row = [];
            foreach ($headers as $col => $header) {
                if ($header !== '') {
                    $row[$header] = $this->clean($cells[$col] ?? '');
                }
            }

            $number = $numHeader ? $row[$numHeader] : '';
            $filled = array_filter($row, 'strlen');
            unset($filled[$numHeader]);

            if ($row[$nameHeader] !== '' && count($filled) >= 2) {
                $rows[] = $row;                                     // a product
            } elseif ($row[$nameHeader] === '' && ($number === '' || is_numeric($number)) && $filled && $rows) {
                foreach ($filled as $header => $value) {            // more lines for the product above
                    $last = &$rows[count($rows) - 1][$header];
                    $last = $last === '' ? $value : $last."\n".$value;
                    unset($last);
                }
            } elseif ($this->texts($cells)) {
                $this->notes = $this->texts($cells);                // text below the table
            }
        }

        return $rows;
    }

    /** Non-empty cell texts of a row, left to right. */
    private function texts(array $cells): array
    {
        return array_values(array_filter(array_map(fn ($c) => $this->clean($c), $cells), 'strlen'));
    }

    /** Trim spaces, including the non-breaking spaces spreadsheets often carry. */
    private function clean(mixed $value): string
    {
        return (string) preg_replace('/^[\s\x{00A0}]+|[\s\x{00A0}]+$/u', '', (string) $value);
    }

    /**
     * Lines of a cell: a line starting in lower case continues the one before
     * ("Addition to vancomycin/metronidazole" + "treatment to prevent …"),
     * "antibiotic- associated" loses its stray space, known typos are fixed.
     *
     * @return list<string>
     */
    private function lines(string $value): array
    {
        $lines = [];

        foreach (preg_split('/\R/u', $value) as $line) {
            $line = $this->clean(preg_replace('/(\p{L})- (\p{L})/u', '$1-$2', preg_replace(array_keys(self::TYPOS), array_values(self::TYPOS), $line)));

            if ($line === '') {
                continue;
            }

            if ($lines && preg_match('/^\p{Ll}/u', $line)) {
                $lines[count($lines) - 1] .= ' '.$line;
            } else {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * Questions and answers from the text below the table.
     *
     * @param  list<string>  $notes
     * @return list<array{form: ?string, question: string, answer: string}>
     */
    private function questions(array $notes): array
    {
        $faqs = [];
        $form = null;

        foreach ($notes as $line) {
            $line = $this->clean(preg_replace(array_keys(self::TYPOS), array_values(self::TYPOS), $line));

            if (str_ends_with($line, '?')) {
                // "1.Bioflor® 250 mg – Website FAQ1. How should I take …?" → "How should I take …?"
                $question = preg_match('/^\d/', $line) ? preg_replace('/^.*\d+\.\s*(?=\p{Lu})/u', '', $line) : $line;
                $faqs[] = ['form' => $form, 'question' => $question, 'answer' => ''];
            } elseif (! preg_match('/[.!:;]$/', $line) && mb_strlen($line) < 80 && ($found = $this->detectForm(strtolower($line)))) {
                $form = $found;                                     // heading: "… Sachet", "Bioflor® 250 mg Capsules"
            } elseif ($faqs) {
                $last = &$faqs[count($faqs) - 1]['answer'];
                $last = trim($last.' '.$line);
                unset($last);
            }
        }

        return array_values(array_filter($faqs, fn ($faq) => $faq['answer'] !== ''));
    }

    private function readCsv(string $file): array
    {
        $grid = [];
        $handle = fopen($file, 'r');
        while (($cells = fgetcsv($handle)) !== false) {
            $grid[] = $cells;
        }
        fclose($handle);

        return $grid;
    }

    /** Minimal .xlsx reader (first sheet, shared and inline strings). */
    private function readXlsx(string $file): array
    {
        $zip = new ZipArchive;
        if ($zip->open($file) !== true) {
            throw new RuntimeException('Cannot open '.basename($file));
        }

        $strings = [];
        if ($xml = $zip->getFromName('xl/sharedStrings.xml')) {
            foreach (simplexml_load_string($xml)->si as $si) {
                $strings[] = trim(html_entity_decode(strip_tags($si->asXML()), ENT_QUOTES | ENT_XML1, 'UTF-8'));
            }
        }

        $sheet = simplexml_load_string((string) $zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();

        $grid = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $col = $this->columnIndex((string) $c['r']);
                $value = (string) $c->v;
                $cells[$col] = match ((string) $c['t']) {
                    's'         => $strings[(int) $value] ?? '',
                    'inlineStr' => trim(strip_tags($c->is->asXML())),
                    default     => $value,
                };
            }
            if ($cells) {
                $grid[] = array_replace(array_fill(0, max(array_keys($cells)) + 1, ''), $cells);
            }
        }

        return $grid;
    }

    private function columnIndex(string $ref): int
    {
        $letters = preg_replace('/\d+/', '', $ref);
        $index = 0;
        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord(strtoupper($char)) - 64);
        }

        return $index - 1;
    }

    // ------------------------------------------------------------ interpret

    private function interpret(array $row): array
    {
        $pick = function (string $pattern) use ($row): ?string {
            foreach ($row as $header => $value) {
                if (preg_match($pattern, $header) && $value !== '') {
                    return $value;
                }
            }

            return null;
        };

        $description = (string) $pick('/^(description|product( name)?|name)$/i');
        $composition = $pick('/compos|ingredient|active/i');
        $packaging   = $pick(self::PACKAGING);
        $id          = $pick('/^(id|ref|reference|sku|code)$/i');

        $haystack = strtolower($description.' '.$packaging);
        $form     = $this->detectForm($haystack);
        $strength = preg_match('/(\d+(?:[.,]\d+)?)\s*mg\b/i', $description, $m) ? (float) str_replace(',', '.', $m[1]) : null;
        $units    = preg_match('/(\d+)\s*[a-z]/i', (string) $packaging, $m) ? (int) $m[1]
            : (preg_match('/\b(\d+)\s*(?:'.implode('|', self::FORMS[$form] ?? ['x']).')\b/i', $description, $m) ? (int) $m[1] : null);

        $brand = brand();
        $formLabel = $form ? Str::plural(ucfirst($form === 'drops' ? 'drop' : $form)) : null;
        $name = str_contains(strtolower($description), strtolower($brand)) && $formLabel
            ? trim($brand.'&reg; '.($strength ? $this->number($strength).'mg ' : '').$formLabel)
            : Str::title(strtolower($description));

        // Other columns fill the attribute they name, or become attributes of their own.
        $extra = collect($row)
            ->reject(fn ($v, $header) => preg_match('/^(no\.?|#|n°|description|product( name)?|name|id|ref|reference|sku|code)$/i', $header)
                || preg_match('/compos|ingredient|active/i', $header)
                || preg_match(self::PACKAGING, $header))
            ->filter(fn ($v) => $v !== '')
            ->all();

        $composition = $composition ? implode(' ', $this->lines($composition)) : null;

        return [
            'source'      => $description,
            'name'        => $name,
            'slug'        => Str::slug(plain_text($name)),
            'id'          => $id && ctype_digit($id) ? (int) $id : null,
            'form'        => $form,
            'strength'    => $strength,
            'units'       => $units,
            'composition' => $composition,
            'packaging'   => $packaging ? strtolower(implode(' ', $this->lines($packaging))) : null,
            'extra'       => $extra,
        ];
    }

    private function detectForm(string $text): ?string
    {
        foreach (self::FORMS as $form => $words) {
            foreach ($words as $word) {
                if (preg_match('/(\b|\d)'.$word.'\b/i', $text)) {
                    return $form;
                }
            }
        }

        return null;
    }

    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private function matchImage(Collection $images, array $data): ?string
    {
        $best = null;
        $bestScore = 0;

        foreach ($images as $file) {
            $name  = strtolower(pathinfo($file, PATHINFO_FILENAME));
            $score = 0;

            if ($data['form'] && $this->detectForm(preg_replace('/[^a-z0-9]+/', ' ', $name)) === $data['form']) {
                $score += 4;
            }
            if ($data['units'] && preg_match('/(^|\D)'.$data['units'].'(\D|$)/', $name)) {
                $score += 2;
            }
            if ($data['strength'] && str_contains($name, $this->number($data['strength']))) {
                $score += 1;
            }

            if ($score > $bestScore) {
                [$best, $bestScore] = [$file, $score];
            }
        }

        return $bestScore >= 4 ? $best : null;
    }

    // ----------------------------------------------------------------- save

    private function save(array $data, ?string $image): Product
    {
        $existing = Product::where('slug', $data['slug'])->first();
        $id = $existing?->id ?? $data['id'] ?? $this->nextId();

        $category = $this->formCategory($data['form']);

        $product = Product::updateOrCreate(['id' => $id], [
            'name'             => $data['name'],
            'slug'             => $data['slug'],
            'attribute_set_id' => $existing?->attribute_set_id ?? AttributeSet::where('code', 'probiotic-medicine')->value('id'),
            'sort_order'       => $data['sort_order'],
            'is_active'        => true,
        ]);

        if ($image) {
            $product->update(['image_path' => $this->storeImage($image, $data['slug'])]);
        }

        $values = array_filter([
            'active_ingredient' => $data['composition'],
            'ingredients'       => $data['composition'] ? '<p>'.e($data['composition'])
                .($data['strength'] && $data['form'] ? ' — '.$this->number($data['strength']).' mg per '.($data['form'] === 'drops' ? 'dose' : $data['form']) : '').'</p>' : null,
            'pack_size'         => $data['packaging'],
            'units_per_pack'    => $data['units'],
            'strength'          => $data['strength'],
            'dosage_form'       => $data['form'],
            'pack_color'        => self::FORM_COLOUR[$data['form']] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        foreach ($data['extra'] as $header => $value) {
            $definition = $this->columnAttribute($header);
            $values[$definition->code] = $this->format($definition, $this->lines($value));
        }

        foreach ($values as $code => $value) {
            $this->setValue($product, $code, $value);
        }

        if ($category) {
            $root = Category::where('code', 'our-products')->first();
            $sync = [$category->id => ['sort_order' => $product->sort_order ?: 0]];
            if ($root) {
                $sync[$root->id] = ['sort_order' => $product->sort_order ?: 0];
            }
            $product->categories()->syncWithoutDetaching($sync);
        }

        return $product;
    }

    private function nextId(): int
    {
        return max(11110, (int) Product::max('id')) + 1;
    }

    private function setValue(Product $product, string $code, mixed $value): void
    {
        $definition = ProductAttribute::where('code', $code)->first();

        if (! $definition) {
            return;
        }

        // Unknown choices extend the option list instead of being dropped.
        if ($definition->type === 'select' && ! array_key_exists($value, $definition->options ?? [])) {
            $definition->update(['options' => ($definition->options ?? []) + [$value => Str::title($value)]]);
        }

        $match  = ['product_id' => $product->id, 'attribute_id' => $definition->id];
        $stored = ProductAttributeValue::where($match + ['locale' => ''])->value('value');
        $value  = $definition->serialize($value);

        if ($stored !== null && $stored !== $value) {
            // The translations were made from the old English text.
            ProductAttributeValue::where($match)->where('locale', '!=', '')->delete();
        }

        ProductAttributeValue::updateOrCreate($match + ['locale' => ''], ['value' => $value]);
    }

    /**
     * Product Q&A from the sheet replace the product's questions in order;
     * a question whose wording changed loses its translations.
     *
     * @param  list<array{question: string, answer: string}>  $faqs
     */
    private function saveQuestions(Product $product, array $faqs): void
    {
        $existing = ProductFaq::where('product_id', $product->id)->orderBy('sort_order')->orderBy('id')->get()->values();

        foreach ($faqs as $i => $faq) {
            $fields = ['question' => $faq['question'], 'answer' => e($faq['answer']), 'sort_order' => $i + 1];
            $row    = $existing[$i] ?? null;

            if (! $row) {
                ProductFaq::create($fields + ['product_id' => $product->id]);
                continue;
            }

            if ($row->getRawOriginal('question') !== $fields['question'] || $row->getRawOriginal('answer') !== $fields['answer']) {
                $row->translations()->delete();
            }

            $row->update($fields);
        }

        foreach ($existing->slice(count($faqs)) as $surplus) {
            $surplus->translations()->delete();
            $surplus->delete();
        }
    }

    /** Value for an attribute from the lines of a cell. */
    private function format(ProductAttribute $definition, array $lines): string
    {
        return match ($definition->type) {
            'list', 'textarea' => implode("\n", $lines),
            'richtext'         => implode('', array_map(fn ($line) => '<p>'.e($line).'</p>', $lines)),
            default            => implode(' ', $lines),
        };
    }

    /** The attribute a column fills: by alias, then by code or name, else a new one. */
    private function columnAttribute(string $header): ProductAttribute
    {
        foreach (self::ALIASES as $pattern => $code) {
            if (preg_match($pattern, $header) && ($definition = ProductAttribute::where('code', $code)->first())) {
                return $definition;
            }
        }

        return ProductAttribute::where('code', Str::snake(Str::slug($header, ' ')))->first()
            ?? ProductAttribute::all()->first(fn ($a) => strcasecmp($a->getRawOriginal('name'), $header) === 0)
            ?? $this->extraAttribute($header);
    }

    private function extraAttribute(string $header): ProductAttribute
    {
        $code = Str::snake(Str::slug($header, ' '));

        return ProductAttribute::firstOrCreate(['code' => $code], [
            'name'               => Str::ucfirst(strtolower($header)),
            'type'               => 'text',
            'attribute_group_id' => AttributeGroup::where('code', 'specs')->value('id'),
            'sort_order'         => (int) ProductAttribute::max('sort_order') + 10,
        ]);
    }

    private function formCategory(?string $form): ?Category
    {
        if (! $form || ! isset(self::FORM_CATEGORY[$form])) {
            return null;
        }

        [$code, $name] = self::FORM_CATEGORY[$form];

        return Category::firstOrCreate(['code' => $code], [
            'parent_id'  => Category::where('code', 'our-products')->value('id'),
            'slug'       => $code,
            'name'       => $name,
            'sort_order' => (int) Category::max('sort_order') + 1,
            'is_active'  => true,
        ]);
    }

    /** Resize to at most 1400 px and save as WebP (PNG if WebP is unavailable). */
    private function storeImage(string $file, string $slug): string
    {
        $dir = public_path('uploads/products');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Pack shots often carry a harmless "incorrect sRGB profile" warning; ignore it.
        set_error_handler(fn () => true);
        $src = match ($ext) {
            'png'         => imagecreatefrompng($file),
            'jpg', 'jpeg' => imagecreatefromjpeg($file),
            'webp'        => imagecreatefromwebp($file),
            default       => false,
        };
        restore_error_handler();

        if (! $src) {
            copy($file, $dir.'/'.$slug.'.'.$ext);

            return 'products/'.$slug.'.'.$ext;
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $scale = min(1, 1400 / max($w, $h));
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        if (function_exists('imagewebp')) {
            imagewebp($dst, $dir.'/'.$slug.'.webp', 86);
            $path = 'products/'.$slug.'.webp';
        } else {
            imagepng($dst, $dir.'/'.$slug.'.png', 8);
            $path = 'products/'.$slug.'.png';
        }

        return $path;
    }
}
