<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Core product record. Everything that varies between product types lives in
 * attribute values (see ProductAttribute); templates ask for attributes by
 * display slot — labels(), badges(), accordion(), steps(), specs().
 */
class Product extends Model
{
    public $incrementing = false;

    protected $keyType = 'int';

    use HasTranslations;

    protected $guarded = [];

    /** Columns with per-language values (see HasTranslations). */
    protected array $translatable = ['name', 'short_description', 'meta_title', 'meta_description'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @var Collection<string, array{definition: ProductAttribute, value: mixed}>|null */
    private ?Collection $attributeMapCache = null;

    // ---- relations ------------------------------------------------------

    public function attributeSet(): BelongsTo
    {
        return $this->belongsTo(AttributeSet::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('sort_order');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ProductFaq::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->latest();
    }

    // ---- scopes ---------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** Eager-load what tiles and product pages read. */
    public function scopeWithCatalogData($query)
    {
        return $query->with(['attributeValues.definition.group', 'images']);
    }

    // ---- attributes -----------------------------------------------------

    /**
     * Values for the current locale keyed by attribute code. A locale-specific
     * value overrides the language-neutral ('') one.
     *
     * @return Collection<string, array{definition: ProductAttribute, value: mixed}>
     */
    public function attributeMap(): Collection
    {
        if ($this->attributeMapCache !== null) {
            return $this->attributeMapCache;
        }

        $locale = app()->bound('storefront.locale_model') ? (string) optional(app('storefront.locale_model'))->code : '';

        $values = $this->relationLoaded('attributeValues')
            ? $this->attributeValues->loadMissing('definition.group')
            : $this->attributeValues()->with('definition.group')->get();

        return $this->attributeMapCache = $values
            ->filter(fn ($row) => $row->definition && ($row->locale === '' || $row->locale === $locale))
            ->sortBy(fn ($row) => $row->locale === '' ? 1 : 0)
            ->unique('attribute_id')
            ->mapWithKeys(fn ($row) => [$row->definition->code => [
                'definition' => $row->definition,
                'value'      => $row->definition->cast($row->value),
            ]])
            ->reject(fn ($row) => $row['value'] === null || $row['value'] === []);
    }

    /** Raw (cast) value of one attribute. */
    public function attr(string $code, mixed $default = null): mixed
    {
        return $this->attributeMap()[$code]['value'] ?? $default;
    }

    /** Formatted value of one attribute ("250 mg", "Sachet", ...). */
    public function attrText(string $code): string
    {
        $row = $this->attributeMap()[$code] ?? null;

        return $row ? $row['definition']->format($row['value']) : '';
    }

    /** Attribute rows that render in a display slot, in group then attribute order. */
    public function attributesFor(string $display): Collection
    {
        return $this->attributeMap()
            ->filter(fn ($row) => optional($row['definition']->group)->display === $display)
            ->sortBy(fn ($row) => (optional($row['definition']->group)->sort_order ?? 0) * 1000 + $row['definition']->sort_order)
            ->values();
    }

    /** Short labels: "Drug / 20 sachets / From 2 years". */
    public function labels(): Collection
    {
        return $this->attributesFor('labels')
            ->map(fn ($row) => $row['definition']->format($row['value']))
            ->filter()
            ->values();
    }

    /** Yes/no attributes that are true, with their icon. */
    public function badges(): Collection
    {
        return $this->attributesFor('badges')
            ->filter(fn ($row) => $row['value'] === true || ($row['definition']->type !== 'boolean' && $row['value']))
            ->map(fn ($row) => ['label' => $row['definition']->name, 'icon' => $row['definition']->icon ?: 'check'])
            ->values();
    }

    /** Accordion panels: title + HTML body. */
    public function accordion(): Collection
    {
        return $this->attributesFor('accordion')->map(function ($row) {
            $definition = $row['definition'];
            $value      = $row['value'];

            $html = match ($definition->type) {
                'list'     => '<ul>'.collect($value)->map(fn ($item) => '<li>'.e($item).'</li>')->implode('').'</ul>',
                'richtext' => $value,
                default    => '<p>'.nl2br(e($definition->format($value))).'</p>',
            };

            return ['title' => $definition->name, 'html' => $html, 'list' => $definition->type === 'list'];
        });
    }

    /** Key facts: name => formatted value. */
    public function specs(): Collection
    {
        return $this->attributesFor('specs')
            ->mapWithKeys(fn ($row) => [$row['definition']->name => $row['definition']->format($row['value'])]);
    }

    /**
     * How-to-use steps. A step may start with an icon key in brackets:
     * "[pour] Empty the sachet into a glass".
     *
     * @return Collection<int, array{icon: string|null, text: string}>
     */
    public function steps(): Collection
    {
        return $this->attributesFor('steps')
            ->flatMap(fn ($row) => (array) $row['value'])
            ->map(function ($line) {
                preg_match('/^\[(?<icon>[a-z0-9-]+)\]\s*(?<text>.*)$/iu', (string) $line, $m);

                return ['icon' => $m['icon'] ?? null, 'text' => $m['text'] ?? (string) $line];
            })
            ->values();
    }

    // ---- presentation helpers -------------------------------------------

    /** Storefront URL shape: /en/{slug}/{id}.html */
    public function getUrlAttribute(): string
    {
        return url('/'.app('storefront.locale').'/'.$this->slug.'/'.$this->id.'.html');
    }

    /** Name without markup or entities, for alt text, JSON and meta tags. */
    public function getPlainNameAttribute(): string
    {
        return html_entity_decode(strip_tags((string) $this->name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Main pack shot: uploaded photo, first gallery image, or the generated placeholder. */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('uploads/'.$this->image_path);
        }

        $first = $this->relationLoaded('images') ? $this->images->first() : $this->images()->first();

        return $first ? $first->url : route('placeholder.pack', ['product' => $this->id]);
    }

    /** Every image for the product page gallery. */
    public function galleryUrls(): Collection
    {
        $urls = collect();

        if ($this->image_path) {
            $urls->push(asset('uploads/'.$this->image_path));
        }

        $urls = $urls->merge(($this->relationLoaded('images') ? $this->images : $this->images()->get())->map->url);

        return $urls->isEmpty() ? collect([route('placeholder.pack', ['product' => $this->id])]) : $urls->values();
    }

    public function getAverageRatingAttribute(): float
    {
        return round((float) $this->reviews()->avg('rating'), 1);
    }
}
