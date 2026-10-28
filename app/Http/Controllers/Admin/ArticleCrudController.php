<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ArticleCrudController extends CrudController
{
    protected string $model      = Article::class;
    protected string $routeName  = 'admin.articles';
    protected string $title      = 'Articles';
    protected string $singular   = 'Article';
    protected string $orderBy    = 'published_at';
    protected string $orderDir   = 'desc';
    protected array  $searchable = ['title', 'slug', 'excerpt'];

    protected function columns(): array
    {
        return [
            'title'        => 'Title',
            'category'     => 'Category',
            'published_at' => 'Published',
            'is_featured'  => 'Featured',
        ];
    }

    protected function fields(): array
    {
        return [
            'title'        => ['type' => 'text', 'label' => 'Title'],
            'slug'         => ['type' => 'text', 'label' => 'Slug', 'help' => 'URL: /en/{slug}.html'],
            'category'     => ['type' => 'select', 'label' => 'Category', 'options' => [
                'gut-health'     => 'Gut health',
                'our-uniqueness' => 'Our uniqueness',
                'news'           => 'News',
            ]],
            'eyebrow'      => ['type' => 'text', 'label' => 'Topic tag', 'help' => 'Shown on the tile and used by the "all news" topic filter, e.g. Nutrition.'],
            'image_path'   => ['type' => 'image', 'label' => 'Cover photo', 'folder' => 'articles'],
            'image'        => ['type' => 'media', 'label' => 'Image from the media library', 'help' => 'Used when no cover photo is uploaded above. Type to search the library.'],
            'excerpt'      => ['type' => 'textarea', 'label' => 'Excerpt', 'rows' => 3],
            'body'         => ['type' => 'richtext', 'label' => 'Body (HTML)', 'rows' => 16],
            'references'   => ['type' => 'richtext', 'label' => 'References (HTML)', 'rows' => 4, 'help' => 'Shown under "See all references".'],
            'read_minutes' => ['type' => 'number', 'label' => 'Read time (minutes)'],
            'published_at' => ['type' => 'datetime', 'label' => 'Publish date', 'help' => 'Leave empty to keep as a draft'],
            'is_featured'  => ['type' => 'checkbox', 'label' => 'Featured'],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'title'        => ['required', 'string', 'max:190'],
            'slug'         => ['required', 'string', 'max:190', Rule::unique('articles', 'slug')->ignore($record?->getKey())],
            'category'     => ['required', 'string', 'max:60'],
            'eyebrow'      => ['nullable', 'string', 'max:60'],
            'image'        => ['nullable', 'string', 'max:190'],
            'image_path'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'excerpt'      => ['nullable', 'string', 'max:1000'],
            'body'         => ['nullable', 'string'],
            'references'   => ['nullable', 'string'],
            'read_minutes' => ['nullable', 'integer', 'min:1', 'max:60'],
            'published_at' => ['nullable', 'date'],
            'is_featured'  => ['nullable'],
        ];
    }
}
