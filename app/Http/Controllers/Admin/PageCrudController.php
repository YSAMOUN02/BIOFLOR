<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PageCrudController extends CrudController
{
    protected string $model      = Page::class;
    protected string $routeName  = 'admin.pages';
    protected string $title      = 'Content pages';
    protected string $singular   = 'Page';
    protected string $orderBy    = 'slug';
    protected array  $searchable = ['title', 'slug'];

    protected function columns(): array
    {
        return ['title' => 'Title', 'slug' => 'Slug', 'updated_at' => 'Updated'];
    }

    protected function fields(): array
    {
        return [
            'title'            => ['type' => 'text', 'label' => 'Title'],
            'slug'             => ['type' => 'text', 'label' => 'Slug', 'help' => 'URL: /en/{slug}'],
            'subtitle'         => ['type' => 'text', 'label' => 'Subtitle'],
            'meta_description' => ['type' => 'textarea', 'label' => 'Meta description', 'rows' => 2],
            'body'             => ['type' => 'richtext', 'label' => 'Body (HTML)', 'rows' => 18],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'title'            => ['required', 'string', 'max:190'],
            'slug'             => ['required', 'string', 'max:190', Rule::unique('pages', 'slug')->ignore($record?->getKey())],
            'subtitle'         => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'body'             => ['nullable', 'string'],
        ];
    }
}
