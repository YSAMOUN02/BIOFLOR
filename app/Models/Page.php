<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasTranslations;

    protected $guarded = [];

    /** Columns with per-language values (see HasTranslations). */
    protected array $translatable = ['title', 'subtitle', 'meta_description', 'body'];

    public function getUrlAttribute(): string
    {
        return url('/'.app('storefront.locale').'/'.$this->slug);
    }
}
