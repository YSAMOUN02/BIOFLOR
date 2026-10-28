<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CategoryCrudController extends CrudController
{
    protected string $model      = Category::class;
    protected string $routeName  = 'admin.categories';
    protected string $title      = 'Categories';
    protected string $singular   = 'Category';
    protected string $orderBy    = 'sort_order';
    protected array  $searchable = ['name', 'code'];

    protected function columns(): array
    {
        return ['name' => 'Name', 'code' => 'Code', 'parent_name' => 'Parent', 'sort_order' => 'Order', 'is_active' => 'Active'];
    }

    protected function fields(): array
    {
        $current = $this->current;

        return [
            'name'        => ['type' => 'text', 'label' => 'Name', 'help' => 'Shown as the tab label.'],
            'code'        => ['type' => 'text', 'label' => 'Code', 'help' => 'Used by page sections, e.g. a product tab section reads the children of "our-products".'],
            'slug'        => ['type' => 'text', 'label' => 'Slug'],
            'parent_id'   => ['type' => 'select', 'label' => 'Parent', 'options' => ['' => '— Top level —'] + Category::whereNull('parent_id')
                ->when($current?->exists, fn ($q) => $q->where('id', '!=', $current->id))->pluck('name', 'id')->all()],
            'description' => ['type' => 'textarea', 'label' => 'Description', 'rows' => 3],
            'image_path'  => ['type' => 'image', 'label' => 'Image', 'folder' => 'categories'],
            'sort_order'  => ['type' => 'number', 'label' => 'Sort order'],
            'is_active'   => ['type' => 'checkbox', 'label' => 'Active'],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'name'        => ['required', 'string', 'max:120'],
            'code'        => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'code')->ignore($record?->getKey())],
            'slug'        => ['required', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')->ignore($record?->getKey())],
            'parent_id'   => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image_path'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif,svg', 'max:5120'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable'],
        ];
    }

    protected function prepare(array $data, ?Model $record = null): array
    {
        $data = parent::prepare($data, $record);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
