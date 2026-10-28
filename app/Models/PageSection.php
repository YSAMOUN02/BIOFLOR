<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * One typed component on a page (Page Designer equivalent). The payload is
 * free-form JSON read with get(); strings may contain tokens:
 *
 *   {brand}    the brand name setting (e.g. Bioflor)
 *   {product}  the current product name on product pages
 */
class PageSection extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload'      => 'array',
        'translations' => 'array',   // {"km": {...text fields only...}}
        'is_visible'   => 'boolean',
    ];

    /** @var array<string, string> */
    private array $tokens = [];

    public function scopeForPage($query, string $pageKey, ?string $region = 'main')
    {
        return $query->where('page_key', $pageKey)
            ->when($region !== null, fn ($q) => $q->where('region', $region))
            ->where('is_visible', true)
            ->orderBy('sort_order');
    }

    /** Blade view that renders this section type: sections/{kebab-type}. */
    public function getViewAttribute(): string
    {
        return 'sections.'.Str::kebab($this->type);
    }

    public function withTokens(array $tokens): static
    {
        $this->tokens = $tokens;

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->replaceTokens(data_get($this->localizedPayload(), $key, $default));
    }

    /**
     * The payload with a language's text laid over it. Translations hold text
     * fields only (same structure, list items by position); empty ones fall
     * back to the default language, and images and links stay shared.
     */
    public function localizedPayload(?string $locale = null): array
    {
        $locale ??= content_locale();
        $payload = (array) $this->payload;
        $overlay = $locale !== '' ? (array) (($this->translations ?? [])[$locale] ?? []) : [];

        return $overlay ? array_replace_recursive($payload, self::withoutEmpty($overlay)) : $payload;
    }

    private static function withoutEmpty(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::withoutEmpty($value);
                if ($data[$key] === []) {
                    unset($data[$key]);
                }
            } elseif ($value === null || $value === '') {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /** Stable id for ARIA wiring between a section's controls and panels. */
    public function uid(string $suffix = ''): string
    {
        return Str::slug($this->type).'-'.$this->id.($suffix !== '' ? '-'.$suffix : '');
    }

    /**
     * Tabs for a product range: an "all" tab plus one per child category.
     *
     * @return Collection<int, array{id: string, label: string, products: Collection}>
     */
    public function productTabs(): Collection
    {
        $root = Category::where('code', $this->get('category', 'our-products'))->first();

        if (! $root) {
            return collect([['id' => 'all', 'label' => $this->get('all_label', __('All the products')),
                'products' => Product::active()->withCatalogData()->get()]]);
        }

        $load = fn (Category $category) => $category->products()
            ->where('is_active', true)
            ->with(['attributeValues.definition.group', 'images'])
            ->get();

        $tabs = collect([[
            'id'       => 'all',
            'label'    => $this->get('all_label', __('All the products')),
            'products' => $load($root),
        ]]);

        foreach ($root->children()->where('is_active', true)->get() as $child) {
            $products = $load($child);

            if ($products->isNotEmpty()) {
                $tabs->push(['id' => $child->code, 'label' => $child->name, 'products' => $products]);
            }
        }

        return $tabs;
    }

    /**
     * Articles for carousels and grids, newest first. Payload keys:
     * slugs (explicit list, kept in that order) or category / tag / featured,
     * plus offset and limit.
     */
    public function articles(): Collection
    {
        $slugs = (array) $this->get('slugs', []);

        if ($slugs) {
            return Article::published()->whereIn('slug', $slugs)->get()
                ->sortBy(fn ($article) => array_search($article->slug, $slugs, true))
                ->values();
        }

        return Article::published()
            ->when($this->get('category'), fn ($q, $category) => $q->where('category', $category))
            ->when($this->get('tag'), fn ($q, $tag) => $q->where('eyebrow', $tag))
            ->when($this->get('featured'), fn ($q) => $q->where('is_featured', true))
            ->skip((int) $this->get('offset', 0))
            ->take((int) $this->get('limit', 6))
            ->get();
    }

    private function replaceTokens(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->replaceTokens($item), $value);
        }

        if (! is_string($value) || ! str_contains($value, '{')) {
            return $value;
        }

        return strtr($value, ['{brand}' => brand()] + $this->tokens);
    }
}
