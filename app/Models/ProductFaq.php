<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductFaq extends Model
{
    public $timestamps = false;

    use HasTranslations;

    protected $guarded = [];

    /** Columns with per-language values (see HasTranslations). */
    protected array $translatable = ['question', 'answer'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Readable product name for admin listings. */
    public function getProductLabelAttribute(): string
    {
        return html_entity_decode(strip_tags($this->product?->name ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
