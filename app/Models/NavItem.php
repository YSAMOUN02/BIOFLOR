<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavItem extends Model
{
    use HasTranslations;

    protected $guarded = [];

    /** Columns with per-language values (see HasTranslations). */
    protected array $translatable = ['label', 'description', 'cta_label'];

    protected $casts = [
        'is_highlight' => 'boolean',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('sort_order');
    }

    /** For the admin list. */
    public function getParentLabelAttribute(): ?string
    {
        return $this->parent?->label;
    }

    /** Children grouped by their mega-menu column heading. */
    public function columns()
    {
        return $this->children->groupBy('column_group');
    }
}
