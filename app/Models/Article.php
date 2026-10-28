<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasTranslations;

    protected $guarded = [];

    /** Columns with per-language values (see HasTranslations). */
    protected array $translatable = ['title', 'excerpt', 'body', 'references'];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured'  => 'boolean',
    ];

    /** Storefront URL shape: /en/{slug}.html */
    public function getUrlAttribute(): string
    {
        return url('/'.app('storefront.locale').'/'.$this->slug.'.html');
    }

    /** Uploaded image, else the artwork path in `image`, else a placeholder. */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('uploads/'.$this->image_path);
        }

        return str_contains((string) $this->image, '/')
            ? media_url($this->image)
            : asset('img/placeholders/article-'.(($this->id % 6) + 1).'.svg');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
