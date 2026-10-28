<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Attribute definition (table `attributes`). Values are stored as strings in
 * product_attribute_values; this class knows how to cast, serialise and
 * format them for its type.
 */
class ProductAttribute extends Model
{
    public const TYPES = [
        'text'        => 'Short text',
        'textarea'    => 'Plain text (multi-line)',
        'richtext'    => 'Rich text (HTML)',
        'number'      => 'Number',
        'boolean'     => 'Yes / no',
        'select'      => 'Single choice',
        'multiselect' => 'Multiple choice',
        'list'        => 'List — one item per line',
        'color'       => 'Colour',
        'image'       => 'Image (path in public/uploads)',
        'url'         => 'Link',
        'date'        => 'Date',
    ];

    protected $table = 'attributes';

    use HasTranslations;

    protected $guarded = [];

    /** Columns with per-language values (see HasTranslations). */
    protected array $translatable = ['name', 'help', 'options'];

    protected $casts = [
        'options'       => 'array',
        'is_required'   => 'boolean',
        'is_filterable' => 'boolean',
        'is_searchable' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function sets(): BelongsToMany
    {
        return $this->belongsToMany(AttributeSet::class, 'attribute_attribute_set', 'attribute_id', 'attribute_set_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }

    /** For the admin list. */
    public function getGroupNameAttribute(): ?string
    {
        return $this->group?->name;
    }

    /** Stored string → PHP value. Empty values come back as null. */
    public function cast(?string $raw): mixed
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        return match ($this->type) {
            'number'      => is_numeric($raw) ? $raw + 0 : null,
            'boolean'     => in_array(strtolower(trim($raw)), ['1', 'true', 'yes', 'on'], true),
            'multiselect' => $this->decodeList($raw),
            'list'        => array_values(array_filter(array_map('trim', preg_split('/\R/u', $raw)), 'strlen')),
            default       => $raw,
        };
    }

    /** Submitted form value → stored string (null clears the value). */
    public function serialize(mixed $input): ?string
    {
        if ($this->type === 'boolean') {
            return $input ? '1' : '0';
        }

        if ($this->type === 'multiselect') {
            $items = array_values(array_filter((array) $input, 'strlen'));

            return $items ? json_encode($items, JSON_UNESCAPED_UNICODE) : null;
        }

        if ($input === null) {
            return null;
        }

        $input = trim((string) $input);

        return $input === '' ? null : $input;
    }

    /** Human-readable value for labels and key facts. */
    public function format(mixed $value): string
    {
        if ($value === null || $value === []) {
            return '';
        }

        return match ($this->type) {
            'select'      => $this->optionLabel($value),
            'multiselect' => collect($value)->map(fn ($v) => $this->optionLabel($v))->implode(', '),
            'number'      => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.').($this->unit ? ' '.$this->unit : ''),
            'boolean'     => $value ? __('Yes') : __('No'),
            'list'        => implode(', ', $value),
            'date'        => rescue(fn () => \Illuminate\Support\Carbon::parse($value)->isoFormat('LL'), (string) $value, false),
            default       => (string) $value.($this->unit ? ' '.$this->unit : ''),
        };
    }

    public function optionLabel(mixed $value): string
    {
        return (string) (($this->options ?? [])[$value] ?? $value);
    }

    private function decodeList(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded)
            ? $decoded
            : array_values(array_filter(array_map('trim', explode(',', $raw)), 'strlen'));
    }
}
