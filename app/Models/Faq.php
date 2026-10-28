<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public $timestamps = false;

    use HasTranslations;

    protected $guarded = [];

    /** Columns with per-language values (see HasTranslations). */
    protected array $translatable = ['question', 'answer'];

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group)->orderBy('sort_order');
    }
}
